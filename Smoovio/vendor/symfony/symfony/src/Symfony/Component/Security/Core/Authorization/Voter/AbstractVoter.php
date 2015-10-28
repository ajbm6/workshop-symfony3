<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Abstract Voter implementation that reduces boilerplate code required to create a custom Voter.
 *
 * @author Roman Marintšenko <inoryy@gmail.com>
 */
abstract class AbstractVoter implements VoterInterface
{
    /**
     * Iteratively check all given attributes by calling isGranted.
     *
     * This method terminates as soon as it is able to return ACCESS_GRANTED
     * If at least one attribute is supported, but access not granted, then ACCESS_DENIED is returned
     * Otherwise it will return ACCESS_ABSTAIN
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object         $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object) {
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $object)) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($attribute, $object, $token)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * Determines if the attribute and object are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param string $object    The object to secure
     *
     * @return bool True if the attribute and object is supported, false otherwise
     */
    abstract protected function supports($attribute, $class);

    /**
     * Perform a single access check operation on a given attribute, object and token.
     * It is safe to assume that $attribute and $object's class pass supports method call.
     *
     * @param string         $attribute
     * @param object         $object
     * @param TokenInterface $token
     *
     * @return bool
     */
    abstract protected function voteOnAttribute($attribute, $object, TokenInterface $token);
}
