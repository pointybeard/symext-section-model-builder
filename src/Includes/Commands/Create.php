<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Commands\SectionModelBuilder;

use Extension_SectionModelBuilder;
use pointybeard\Symphony\Extensions\Console;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Symphony\Extensions\SectionModelBuilder;
use pointybeard\Helpers\Foundation\BroadcastAndListen;
use pointybeard\Symphony\Extensions\Console\Commands\Console\Symphony;
use pointybeard\Symphony\SectionBuilder;
use pointybeard\Helpers\Functions\Strings;
use SymphonyPDO;

class Create extends Console\AbstractCommand implements Console\Interfaces\AuthenticatedCommandInterface, BroadcastAndListen\Interfaces\AcceptsListenersInterface
{
    use BroadcastAndListen\Traits\HasListenerTrait;
    use BroadcastAndListen\Traits\HasBroadcasterTrait;
    use Console\Traits\hasCommandRequiresAuthenticateTrait;

    public function __construct()
    {
        parent::__construct();
        $this
            ->description('')
            ->version('1.0.0')
            ->example(
                'symphony -t 4141e465 create articles "\\\\My\\\\Extension\\\\Models" -c Article -o /path/to/models/Article.php'
            )
            ->support("If you believe you have found a bug, please report it using the GitHub issue tracker at https://github.com/pointybeard/symext-section-model-builder/issues, or better yet, fork the library and submit a pull request.\r\n\r\nCopyright 2020 Alannah Kearney. See ".realpath(__DIR__.'/../LICENCE')." for full software licence information.\r\n")
        ;
    }

    public function init(): void
    {
        parent::init();

        $this
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('section')
                    ->flags(Input\AbstractInputType::FLAG_REQUIRED)
                    ->description('the handle of the section to build a model for')
                    ->validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            $section = $context->find('section');
                            if (false == (SectionBuilder\Models\Section::loadFromHandle($section) instanceof SectionBuilder\Models\Section)) {
                                throw new Console\Exceptions\ConsoleException('Section with that handle could not be located.');
                            }

                            return $section;
                        }
                    )
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('namespace')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('namespace that the model will use')
                    ->validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            $namespace = $context->find('namespace');

                            if(false == preg_match("@^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\\\\]*[a-zA-Z0-9_\x7f-\xff]$@", $namespace)) {
                                throw new Console\Exceptions\ConsoleException('Namespace specified is invalid.');
                            }

                            return $namespace;
                        }
                    )
                    ->default(null)
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('class')
                    ->short('c')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
                    ->description('class name for the model')
                    ->validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            $className = $context->find('class');
                            if(false == preg_match("@^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$@", $className)) {
                                throw new Console\Exceptions\ConsoleException('Class name specified is invalid.');
                            }

                            return $className;
                        }
                    )
                    ->default(null)
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('output')
                    ->short('o')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
                    ->description('path to save model class to')
                    ->default(null)
            )
        ;
    }

    public function execute(Input\Interfaces\InputHandlerInterface $input): bool
    {
  
        $customFieldMappings = [];
        $sectionHandle = $input->find('section');
        $className = $input->find('class') ?? ucfirst($sectionHandle);
        $customFieldMappingTemplate = file_get_contents(__DIR__ . '/../Templates/FieldMapping.txt');

        // Get a list of fields for this section
        $query = SymphonyPDO\Loader::instance()->prepare(
            "SELECT id, label, element_name, type FROM `tbl_fields` WHERE `parent_section` = :sectionId ORDER BY `element_name` ASC"
        );

        $query->execute([":sectionId" => \SectionManager::fetchIDFromHandle($sectionHandle)]);

        foreach(new SymphonyPDO\Lib\ResultIterator('\stdClass', $query) as $f) {

            $classMemberName = null;
            $databaseFieldNames = [];
            $namespace = null == $input->find('namespace') ? "" : "namespace " . $input->find('namespace') . ";";

            // Generate the class member name
            $bits = explode('-', $f->element_name);
            $classMemberName = array_shift($bits);
            if (count($bits) > 0) {
                $classMemberName .= implode('', array_map('ucfirst', $bits));
            }

            // Get all the table fields to build the databaseFieldNames
            $q = SymphonyPDO\Loader::instance()->prepare(
                "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE `TABLE_NAME` = :table
                AND `TABLE_SCHEMA` = :schema
                AND COLUMN_NAME NOT IN ('id', 'entry_id')"
            );
            $tableName = "tbl_entries_data_{$f->id}";
            $schemaName = SymphonyPDO\Loader::getCredentials()->db;
            $result = $q->execute([":table" => $tableName, ":schema" => $schemaName]);

            $databaseFieldNames = $q->fetchAll(\PDO::FETCH_COLUMN);

            $customFieldMappings[] = Strings\replace_placeholders_in_string(
                ['FIELD_ID', 'FIELD_NAME', 'FIELD_TYPE', 'ELEMENT_NAME', 'DATABASE_FIELD_NAMES', 'MEMBER_NAME'],
                [$f->id, $f->label, $f->type, $f->element_name, implode('/', $databaseFieldNames), $classMemberName],
                $customFieldMappingTemplate
            );
        }

        $output = Strings\replace_placeholders_in_string(
            ['NAMESPACE', 'DATE_GENERATED', 'CLASS_NAME', 'SECTION', 'CUSTOM_FIELD_MAPPINGS'],
            [$namespace, \DateTimeObj::get('c'), $className, $sectionHandle, implode(PHP_EOL, $customFieldMappings)],
            file_get_contents(__DIR__ . '/../Templates/Model.txt')
        );

        // Condense the resultant code a little by removing unnecesary return
        // characters
        $output = preg_replace("@[\r\n]{3}@", PHP_EOL, $output);

        if (null === $input->find('output')) {
            echo $output.PHP_EOL;
        } else {
            file_put_contents($input->find('output'), $output);
            $this->broadcast(
                Symphony::BROADCAST_MESSAGE,
                E_NOTICE,
                (new Cli\Message\Message())
                    ->message(filesize($input->find('output')).' bytes written to '.$input->find('output'))
                    ->foreground(Colour::FG_GREEN)
            );
        }

        return true;
    }
}
