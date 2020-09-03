<?php
namespace tests;

use extas\components\conditions\Condition;
use extas\components\conditions\ConditionEqual;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\transitions\dispatchers\DateTime;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class DateTimeTest
 *
 * @author jeyroik@gmail.com
 */
class DateTimeTest extends TestCase
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
        $entity = new Entity();
        $result = new TransitResult();
        $dispatcher = new DateTime();
        $dispatcher->addParametersByValues([
            'datetime' => time()-100,
            'compare' => '='
        ]);
        $this->getMagicClass('conditions')->create(new Condition([
            Condition::FIELD__NAME => 'eq',
            Condition::FIELD__ALIASES => ['eq', '='],
            Condition::FIELD__CLASS => ConditionEqual::class
        ]));

        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
    }
}
