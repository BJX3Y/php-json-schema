<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;

use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\RemoteRef\Preloaded;
use Swaggest\JsonSchema\SchemaLoader;

class SpecTest extends \PHPUnit_Framework_TestCase
{
    private static $refProvider;

    public function setUp()
    {
        self::$refProvider = new Preloaded();
        self::$refProvider
            ->setSchemaData(
                'http://localhost:1234/integer.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/integer.json')))
            ->setSchemaData(
                'http://localhost:1234/subSchemas.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/subSchemas.json')))
            ->setSchemaData(
                'http://localhost:1234/folder/folderInteger.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/folder/folderInteger.json')));

        $this->numIterations = 100;
    }

    /**
     * @dataProvider provider
     * @param $schemaData
     * @param $data
     * @param $isValid
     * @throws InvalidValue
     */
    public function testSpecDraft4($schemaData, $data, $isValid)
    {
        $actualValid = true;
        $error = '';

        if ($isValid) {
            $schema = SchemaLoader::create()->setRemoteRefProvider(self::$refProvider)->readSchema($schemaData);
            $res = $schema->import($data);
            $this->assertEquals($data, $schema->export($res));
        } else {
            try {
                $schema = SchemaLoader::create()->setRemoteRefProvider(self::$refProvider)->readSchema($schemaData);
                $res = $schema->import($data);
                $this->assertEquals($data, $schema->export($res));
            } catch (InvalidValue $exception) {
                $actualValid = false;
                $error = $exception->getMessage();
            }

            $this->assertSame($isValid, $actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
                . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT)
                . "\nError: " . $error . "\n");

        }
    }


    public function provider()
    {
        $path = __DIR__ . '/../../../../spec/JSON-Schema-Test-Suite/tests/draft4';
        if (!file_exists($path)) {
            //$this->markTestSkipped('No spec tests found, please run `git submodule bla-bla`');
        }

        $testCases = array();

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if ('.json' !== substr($entry, -5)) {
                        continue;
                    }
                    //die('!1');

                    //echo "$entry\n";
                    /** @var _SpecTest[] $tests */
                    $tests = json_decode(file_get_contents($path . '/' . $entry));
                    ///print_r($tests);
                    foreach ($tests as $test) {
                        //$schema = SchemaLoader::create()->readSchema($test->schema);
                        foreach ($test->tests as $case) {
                            $testCases[$entry . ' ' . $test->description . ': ' . $case->description] = array(
                                'schema' => $test->schema,
                                'data' => $case->data,
                                'isValid' => $case->valid,
                            );
                        }
                    }
                }
            }
            closedir($handle);
        }

        //print_r($testCases);

        return $testCases;
    }


}

/**
 * @property $description
 * @property $schema
 * @property _SpecTestCase[] $tests
 */
class _SpecTest
{
}

/**
 * @property $description
 * @property $data
 * @property bool $valid
 */
class _SpecTestCase
{
}