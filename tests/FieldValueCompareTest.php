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

    public function testEqual()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
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
        $result = new TransitionResult();
        $dispatcher = new FieldValueCompare();
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testNotEqual()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'notEqual'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'not test'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testGreater()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'greater'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'west'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testGreaterOrEqual()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'greaterOrEqual'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'west'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);

        $entity['test'] = 'test';
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testLower()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'lower'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'tes'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testLowerOrEqual()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'lowerOrEqual'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'tes'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);

        $entity['test'] = 'test';
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testEmpty()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'empty'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => ''
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testNotEmpty()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'notEmpty'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
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
        $result = new TransitionResult();
        $dispatcher = new FieldValueCompare();
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testLike()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'like'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'tester'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testNotLike()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'notLike'
                ],
                [
                    IParameter::FIELD__NAME => 'field_value',
                    IParameter::FIELD__VALUE => 'test'
                ],
                [
                    IParameter::FIELD__NAME => 'field_type',
                    IParameter::FIELD__VALUE => 'string'
                ]
            ]
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'success'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testIn()
    {
        $test = new TransitionDispatcher([
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
        ]);
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
        $result = new TransitionResult();
        $dispatcher = new FieldValueCompare();
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testNotIn()
    {
        $test = new TransitionDispatcher([
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
                    IParameter::FIELD__VALUE => 'notIn'
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
        ]);
        $entity = new WorkflowEntity([
            WorkflowEntity::FIELD__STATE => 'from',
            WorkflowEntity::FIELD__TEMPLATE => 'test',
            'test' => 'success'
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
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertTrue($accepted);
    }

    public function testMissedParameter()
    {
        $test = new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TEMPLATE => 'test',
            TransitionDispatcher::FIELD__PARAMETERS => [
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
        ]);
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
        $result = new TransitionResult();
        $dispatcher = new FieldValueCompare();
        $accepted = $dispatcher(
            $test,
            $transition,
            $entity,
            $schema,
            $context,
            $result,
            $entity
        );

        $this->assertFalse($accepted);
        $this->assertEquals(
            ITransitionErrorVocabulary::ERROR__VALIDATION_FAILED,
            $result->getError()->getCode()
        );
    }
}
