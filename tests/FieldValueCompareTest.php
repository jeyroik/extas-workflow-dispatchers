<?php

use PHPUnit\Framework\TestCase;
use extas\components\workflows\entities\WorkflowEntityTemplateRepository;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\workflows\entities\WorkflowEntityTemplate;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplateRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate as TDT;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\entities\WorkflowEntity;
use extas\components\workflows\entities\WorkflowEntityContext;

use extas\components\workflows\Workflow;
use extas\components\workflows\transitions\results\TransitionResult;
use extas\components\workflows\transitions\dispatchers\FieldValueCompare;
use extas\interfaces\workflows\transitions\errors\ITransitionErrorVocabulary;

/**
 * Class FieldValueCompareTest
 *
 * @author jeyroik@gmail.com
 */
class FieldValueCompareTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $entityTemplateRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionDispatcherRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionTemplateDispatcherRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->entityTemplateRepo = new WorkflowEntityTemplateRepository();
        $this->transitionDispatcherRepo = new TransitionDispatcherRepository();
        $this->transitionDispatcherTemplateRepo = new TransitionDispatcherTemplateRepository();
        $this->transitionRepo = new WorkflowTransitionRepository();

        SystemContainer::addItem(
            ITransitionDispatcherRepository::class,
            TransitionDispatcherRepository::class
        );
        SystemContainer::addItem(
            ITransitionDispatcherTemplateRepository::class,
            TransitionDispatcherTemplateRepository::class
        );
        SystemContainer::addItem(
            IWorkflowEntityTemplateRepository::class,
            WorkflowEntityTemplateRepository::class
        );
        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->entityTemplateRepo->delete([WorkflowEntityTemplate::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionDispatcherTemplateRepo->delete([TDT::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
    }

    public function testCondition()
    {
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'test'
        ]);

        $schema = new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test',
            WorkflowSchema::FIELD__TRANSITIONS => ['test']
        ]);

        $context = new WorkflowEntityContext([
            'test' => true
        ]);

        $transition = new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]);
        $this->transitionRepo->create($transition);

        $this->entityTemplateRepo->create(new WorkflowEntityTemplate([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__CLASS => WorkflowEntityContext::class
        ]));

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TEMPLATE => 'test',
            TransitionDispatcher::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => 'field_name',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_compare',
                    IParameter::FIELD__VALUE => 'equal'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => FieldValueCompare::TYPE__STRING
                ]
            ]
        ]));

        $this->transitionDispatcherTemplateRepo->create(new TDT([
            TDT::FIELD__NAME => 'test',
            TDT::FIELD__TITLE => '',
            TDT::FIELD__DESCRIPTION => '',
            TDT::FIELD__CLASS => 'extas\\components\\workflows\\transitions\\dispatchers\\FieldValueCompare',
            TDT::FIELD__PARAMETERS => []
        ]));

        $workflow = new Workflow();
        $result = new TransitionResult();

        $this->assertTrue($workflow->isTransitionValid(
            $transition,
            $entity,
            $schema,
            $context,
            $result
        )->isSuccess());
    }

    public function testEqualStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'equal',
            'test',
            FieldValueCompare::TYPE__STRING,
            'test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testEqualNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'equal',
            5,
            FieldValueCompare::TYPE__NUMBER,
            5
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testNotEqual()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'notEqual',
            'test',
            FieldValueCompare::TYPE__STRING,
            'not test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testGreaterStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'greater',
            'test',
            FieldValueCompare::TYPE__STRING,
            'west'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testGreaterNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'greater',
            1,
            FieldValueCompare::TYPE__NUMBER,
            5
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testGreaterOrEqual()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'greaterOrEqual',
            'test',
            FieldValueCompare::TYPE__STRING,
            'west'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);

        $entity['test'] = 'test';
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testLowerStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'lower',
            'test',
            FieldValueCompare::TYPE__STRING,
            'nest'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testLowerNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'lower',
            100,
            FieldValueCompare::TYPE__NUMBER,
            5
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testLowerOrEqual()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'lowerOrEqual',
            'test',
            FieldValueCompare::TYPE__STRING,
            'nest'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);

        $entity['test'] = 'test';
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testEmptyStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'empty',
            '',
            FieldValueCompare::TYPE__STRING,
            ''
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testEmptyNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'empty',
            '',
            FieldValueCompare::TYPE__NUMBER,
            0
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testNotEmpty()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'notEmpty',
            '',
            FieldValueCompare::TYPE__STRING,
            'test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testLikeStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'like',
            'test',
            FieldValueCompare::TYPE__STRING,
            'tester'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testLikeNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'like',
            55,
            FieldValueCompare::TYPE__NUMBER,
            5
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testNotLike()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'notLike',
            'test',
            FieldValueCompare::TYPE__STRING,
            'success'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testInStrings()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'in',
            ['test'],
            FieldValueCompare::TYPE__STRING,
            'test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testInNotArray()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'in',
            'test',
            FieldValueCompare::TYPE__STRING,
            'test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testInNumbers()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'in',
            [5],
            FieldValueCompare::TYPE__NUMBER,
            5
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testNotIn()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'notIn',
            ['test'],
            FieldValueCompare::TYPE__STRING,
            'success'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testUnknownCompareAsEqual()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'unknown',
            'test',
            FieldValueCompare::TYPE__STRING,
            'test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertTrue($accepted);
    }

    public function testInvalid()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'equal',
            'test',
            FieldValueCompare::TYPE__STRING,
            'not test'
        );
        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertFalse($accepted);
        $this->assertEquals(
            ITransitionErrorVocabulary::ERROR__VALIDATION_FAILED,
            $result->getError()->getCode()
        );
    }

    public function testMissedParameter()
    {
        list($test, $entity, $schema, $context, $transition, $result, $dispatcher) = $this->getFixtureData(
            'in',
            '-',
            FieldValueCompare::TYPE__STRING,
            '',
            [
                [
                    IParameter::FIELD__NAME => 'field_compare',
                    IParameter::FIELD__VALUE => 'in'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => ['test']
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        );

        $accepted = $dispatcher($test, $transition, $entity, $schema, $context, $result, $entity);
        $this->assertFalse($accepted);
        $this->assertEquals(
            ITransitionErrorVocabulary::ERROR__VALIDATION_FAILED,
            $result->getError()->getCode()
        );
    }

    protected function getFixtureData(
        string $fieldCompare,
        $fieldValue,
        string $fieldType,
        $entityValue,
        array $parameters = []
    )
    {
        $parameters = empty($parameters)
            ? [
                [
                    IParameter::FIELD__NAME => 'field_name',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_compare',
                    IParameter::FIELD__VALUE => $fieldCompare
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => $fieldValue
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => $fieldType
                ]
            ]
            : $parameters;

        $test = new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TEMPLATE => 'test',
            TransitionDispatcher::FIELD__PARAMETERS => $parameters
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => $entityValue
        ]);

        $schema = new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test',
            WorkflowSchema::FIELD__TRANSITIONS => ['test']
        ]);

        $context = new WorkflowEntityContext([
            'test' => true
        ]);

        $transition = new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]);
        $result = new TransitionResult();
        $dispatcher = new FieldValueCompare();

        return [
            $test, $entity, $schema, $context, $transition, $result, $dispatcher
        ];
    }
}
