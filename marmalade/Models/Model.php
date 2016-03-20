<?php
namespace Marmalade\Models;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** Base Model class 
 * This class should be sub-classed for all models 
 */
abstract class Model {}