<?php
namespace extas\interfaces\workflows\transitions\dispatchers;

use extas\interfaces\IItem;
use extas\interfaces\samples\parameters\IHasSampleParameters;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Interface INotifier
 *
 * @package extas\interfaces\workflows\transitions\dispatchers
 * @author jeyroik@gmail.com
 */
interface INotifier extends IHasSampleParameters
{
    /**
     * @param IEntity $entity
     * @param IItem $context
     * @param ITransitResult $result
     */
    public function notify(IEntity $entity, IItem $context, ITransitResult &$result): void;
}
