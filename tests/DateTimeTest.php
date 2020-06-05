<?php
namespace tests;

use extas\components\repositories\TSnuffRepository;
use extas\components\conditions\Condition;
use extas\components\conditions\ConditionEqual;
use extas\components\conditions\ConditionRepository;
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
    use TSnuffRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->registerSnuffRepos(['conditionRepository' => ConditionRepository::class]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
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
        $this->createWithSnuffRepo('conditionRepository', new Condition([
            Condition::FIELD__NAME => 'eq',
            Condition::FIELD__ALIASES => ['eq', '='],
            Condition::FIELD__CLASS => ConditionEqual::class
        ]));

        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
    }
}
