<?php
namespace tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

use extas\components\workflows\transits\TransitResult;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\transitions\dispatchers\EntityHasAllParams;

/**
 * Class EntityHasAllParamsTest
 *
 * @author jeyroik@gmail.com
 */
class EntityHasAllParamsTest extends TestCase
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
        $dispatcher = new EntityHasAllParams();
        $dispatcher->addParametersByValues(['test' => true]);
        $dispatcher($result, $entity);
        $this->assertTrue($result->hasErrors());
    }
}
