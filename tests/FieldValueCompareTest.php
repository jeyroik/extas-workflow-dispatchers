<?php
namespace tests;

use extas\components\repositories\TSnuffRepository;
use extas\components\conditions\Condition;
use extas\components\conditions\ConditionEqual;
use extas\components\conditions\ConditionRepository;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\transitions\dispatchers\FieldValueCompare;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

/**
 * Class FieldValueCompareTest
 *
 * @author jeyroik@gmail.com
 */
class FieldValueCompareTest extends TestCase
{
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['conditions', 'name', Condition::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testConditionFailed()
    {
        $entity = new Entity([
            'test' => 5
        ]);

        $dispatcher = new FieldValueCompare();
        $dispatcher->addParametersByValues([
            'field_name' => 'test',
            'field_value' => 8,
            'field_compare' => '='
        ]);

        $this->getMagicClass('conditions')->create(new Condition([
            Condition::FIELD__NAME => 'eq',
            Condition::FIELD__ALIASES => ['eq', '='],
            Condition::FIELD__CLASS => ConditionEqual::class
        ]));

        $result = new TransitResult();
        $dispatcher($result, $entity);

        $this->assertTrue($result->hasErrors());
    }
}
