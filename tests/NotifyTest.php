<?php
namespace tests;

use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transitions\dispatchers\Notify;
use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class NotifyTest
 *
 * @author jeyroik@gmail.com
 */
class NotifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
    }

    public function testConditionFailed()
    {
        $entity = new Entity();
        $result = new TransitResult();
        $dispatcher = new Notify([Notify::FIELD__CONTEXT => new EntityContext()]);
        $dispatcher->addParametersByValues([Notify::FIELD__NOTIFIER_CLASS => NotifierError::class]);
        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
        $errors = $result->getErrors();
        $notifierError = array_shift($errors);
        $this->assertEquals(400, $notifierError->getCode());
        $this->assertEquals('can not notify', $notifierError->getName());
    }
}
