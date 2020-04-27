<?php
namespace tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transitions\dispatchers\ContextHasAllParams;

/**
 * Class ContextHasAllParamsTest
 *
 * @author jeyroik@gmail.com
 */
class ContextHasAllParamsTest extends TestCase
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
        $context = new EntityContext();
        $result = new TransitResult();
        $dispatcher = new ContextHasAllParams([ContextHasAllParams::FIELD__CONTEXT => $context]);
        $dispatcher->addParametersByValues(['test' => true]);
        $dispatcher($result, $entity);

        $this->assertTrue($result->hasErrors());
    }
}
