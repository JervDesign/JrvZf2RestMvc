<?php
 /**
 * module.config.php
 *
 * ZF2 Module Config file
 *
 * PHP version 5
 *
  * @category  ZF2
  * @package   JrvZf2RestMvc
  * @author    James Jervis <james@jervdesign.com>
  * @copyright 2015 JervDesign
  * @license   License.txt
  * @version   Release: <package_version>
  * @link      https://github.com/JervDesign
  */
return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],

    'service_manager' => [
        'factories' => [
//            'Zf2Rest\Mvc\SOMETHING'
//            => 'Zf2Rest\Mvc\SOMETHING',
         ],
     ]
 ];