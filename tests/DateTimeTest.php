<?php
namespace tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

use extas\components\conditions\Condition;
use extas\components\conditions\ConditionEqual;
use extas\components\conditions\ConditionRepository;
use extas\components\workflows\transits\TransitResult;
use extas\interfaces\conditions\IConditionRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\transitions\dispatchers\DateTime;

/**
 * Class DateTimeTest
 *
 * @author jeyroik@gmail.com
 */
class DateTimeTest extends TestCase
{
    protected ?IRepository $condRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->condRepo = new ConditionRepository();

        SystemContainer::addItem(
            IConditionRepository::class,
            ConditionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->condRepo->delete([Condition::FIELD__NAME => 'eq']);
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
        $this->condRepo->create(new Condition([
            Condition::FIELD__NAME => 'eq',
            Condition::FIELD__ALIASES => ['eq', '='],
            Condition::FIELD__CLASS => ConditionEqual::class
        ]));

        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
    }
}
