<?php
namespace tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

use extas\components\workflows\transitions\dispatchers\Notify;
use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;

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
        $dispatcher = new Notify();
        $dispatcher->addParametersByValues([Notify::FIELD__NOTIFIER_CLASS => NotifierError::class]);
        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
        $errors = $result->getErrors();
        $notifierError = array_shift($errors);
        $this->assertEquals(400, $notifierError->getCode());
        $this->assertEquals('can not notify', $notifierError->getName());
    }
}
