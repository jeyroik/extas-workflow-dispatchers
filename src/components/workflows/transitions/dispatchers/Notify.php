<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\INotifier;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Class Notify
 *
 * @package extas\components\workflows\transitions\dispatchers
 * @author jeyroik@gmail.com
 */
class Notify extends TransitionDispatcherExecutor
{
    public const FIELD__NOTIFIER_CLASS = 'notifier_class';

    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $notifierClass = $this->getParameterValue(static::FIELD__NOTIFIER_CLASS);
        /**
         * @var INotifier $notifier
         */
        $notifier = new $notifierClass($this->__toArray());
        $notifier->notify($entityEdited, $this->getContext(), $result);

        return true;
    }
}
