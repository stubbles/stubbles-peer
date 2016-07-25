<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\peer
 */
 namespace stubbles\peer
 {
     class CheckdnsrrResult
     {
         public static $value = null;
     }

     function checkdnsrr(string $host, string $type = 'MX')
     {
         if (null !== CheckdnsrrResult::$value) {
             return CheckdnsrrResult::$value;
         }

         return \checkdnsrr($host, $type);
     }
}
namespace stubbles\peer\http
{
    class CheckdnsrrResult
    {
        public static $value = null;
    }

    function checkdnsrr(string $host, string $type = 'MX')
    {
        if (null !== CheckdnsrrResult::$value) {
            return CheckdnsrrResult::$value;
        }

        return \checkdnsrr($host, $type);
    }
}
