<?php
/**
 * CActiveRecordInheritance class file.
 *
 * @author Javier Juan <javijuol@gmail.com>
 * @link http://www.yiiframework.com/
 * @version 0.2
 * @package extensions\CActiveRecordInheritance
 *
 */

/**
 *
 *  The CActiveRecordInheritance extension adds up table inheritance to AR models.
 *
 * To use this extension, just copy this file to your extensions/ directory,
 * add 'import' => 'application.extensions.CActiveRecordInheritance', [...] to your
 * config/main.php and add this behavior to each child model you would like to
 * inherit the new possibilities:
 *
 *      public function behaviors()
 *      {
 *          return array( 'CActiveRecordInheritance' => array(
 *              'class' => 'application.extensions.CActiveRecordInheritance'
 *          ));
 *      }
 *
 *
 * ========= BEGIN EXAMPLE ==========
 * class Car extends CActiveRecord {
 *  // property integer $id
 *  // property string  $name
 *      ...
 * }
 *
 * class SportCar extends Car {
 *  // property integer $car_id
 *  // property number  $power
 *      ...
 *
 *      public function behaviors()
 *      {
 *          return array( 'CActiveRecordInheritance' => array(
 *              'class' => 'application.extensions.CActiveRecordInheritance'
 *          ));
 *      }
 * }
 *
 * class FamilyCar extends Car {
 *  // property integer $car_id
 *  // property integer $seats
 *      ...
 *
 *      public function behaviors()
 *      {
 *          return array( 'CActiveRecordInheritance' => array(
 *              'class' => 'application.extensions.CActiveRecordInheritance'
 *          ));
 *      }
 * }
 *
 *
 * $sportCar = new SportCar();
 * $sportCar->name  = 'FastCar';
 * $sportCar->power = 120;
 * $sportCar->save();
 *
 * $familyCars = FamilyCar::model()->findAllByAttributes(array('seats'=>8));
 *
 * ========= END EXAMPLE ==========
 *
 */

class CActiveRecordInheritance extends CActiveRecordBehavior
{
    public $parent, $child;
    private static $_m;
    private static $tmp;


    /**
     * Each time the child model is called it will be attached
     * automatically to the 'extendedModel' scope.
     *
     * @param CEvent $event
     */
    public function afterConstruct($event){
        if(method_exists($this->owner,'beforeAfterConstruct')) $this->owner->beforeAfterConstruct(); // Fallback

        $this->extendedModel();
        parent::afterConstruct($event);
    }

    /**
     * Save temporarily the parent and child instances, because find() returns
     * another instance and that's why the object variables won't remain, so
     * in order to get the objects instantiated after the constructor it
     * save them.
     *
     * @param CEvent $event
     *
     * @return bool
     */
    public function beforeFind($event)
    {
        if(method_exists($this->owner,'beforeBeforeFind')) $this->owner->beforeBeforeFind(); // Fallback

        if(isset(self::$_m[get_class($this->parent).'->'.get_class($this->child)]))
            self::$tmp = self::$_m[get_class($this->parent).'->'.get_class($this->child)];
        return parent::beforeFind($event);
    }

    /**
     * Load the parent and child objects after the find() method, so it can keep track of
     * the changes it makes at the 'scope time'.
     *
     * @param CEvent $event
     */
    public function afterFind($event)
    {
        if(method_exists($this->owner,'beforeAfterFind')) $this->owner->beforeAfterFind(); // Fallback

        if(!empty(self::$tmp)){
            $this->parent = self::$tmp['parent'];
            $this->child = self::$tmp['child'];
            self::$tmp = null;
        }
        parent::afterFind($event);
    }

    /**
     * Validate the parent model before the child model.
     *
     * @param CEvent $event
     *
     * @return bool
     */
    public function beforeValidate($event)
    {
        if(method_exists($this->owner,'beforeAfterFind')) $this->owner->beforeBeforeValidate(); // Fallback

        if(!is_null($this->parent)&&!is_null($this->child)){
            if(!$this->parent->validate()) return false;
        }
        return parent::beforeValidate($event);
    }

    /**
     * Fill the parent object with the attribute values extended from the parent, as they
     * have not been setted directly in the parent object but it sets in the child extended
     * attributes from the parent.
     * Finally, it saves parent changes before the child is saved.
     *
     * @param CModelEvent $event
     *
     * @return bool
     *
     * @throws CException
     */
    public function beforeSave($event)
    {
        if(method_exists($this->owner,'beforeBeforeSave')) $this->owner->beforeBeforeSave(); // Fallback

        if(!is_null($this->parent)&&!is_null($this->child)){
            $attributes = $this->owner->attributes;
            $this->owner->getDbConnection()->getSchema()->getTable($this->owner->tableName(),true);
            $this->owner->refreshMetadata();
            foreach($this->child->attributes AS $attr => $val){
                if(!in_array($attr,array_keys($this->owner->tableSchema->columns))){
                    unset($this->owner->tableSchema->columns[$attr]);
                    $this->parent->$attr = $attributes[$attr];
                }
            }
            $this->parent->isNewRecord = $this->owner->isNewRecord;
            if(!$this->parent->save(false))
                throw new CException('The parent model has not been saved.');
            $this->owner->primaryKey = $this->parent->primaryKey;
        }
        return parent::beforeSave($event);
    }

    /**
     * Load extended attributes in the child model after save changes occur
     *
     * @param CModelEvent $event
     */
    public function afterSave($event)
    {
        if(method_exists($this->owner,'beforeAfterSave')) $this->owner->beforeAfterSave(); // Fallback

        if($this->owner->isNewRecord){
            $this->child->tableSchema->columns = array_merge($this->parent->tableSchema->columns,$this->child->tableSchema->columns);
            $this->child->refreshMetaData();
            $this->owner->{$this->parent->tableSchema->primaryKey} = $this->owner->primaryKey;
            foreach($this->parent->attributes AS $attr => $val){
                $this->owner->$attr = $val;
            }
        }
    }

    /**
     * Scope that joins the extended model (which must be an AR too)
     * and also mix the rules, relations and attributes in one single model.
     * Have to be careful with duplicated names, because this behavior can't
     * distinguish between ambiguos columns in parent and child models.
     * The child model should have its PK not autoincremental, cause it will
     * be setted with the same as its parent.
     * The table alias for the parent model will be 'p', and the table alias
     * for the child model still remains 't'.
     *
     * @return CActiveRecord
     */
    public function extendedModel()
    {
        $parent = get_parent_class($this->owner);

        $this->child  = $this->owner;
        $this->parent = new $parent();
        $this->parent->setTableAlias('p');

        $this->child->getDbCriteria()->mergeWith(array(
            'select'=>"{$this->parent->getTableAlias(false)}.*,{$this->child->getTableAlias(false)}.*",
            'join'=>"INNER JOIN {$this->parent->tableName()} {$this->parent->getTableAlias(false)} ON {$this->child->getTableAlias(false)}.{$this->child->tableSchema->primaryKey} = {$this->parent->getTableAlias(false)}.{$this->parent->tableSchema->primaryKey}",
        ));

        $this->child->tableSchema->columns = array_merge($this->parent->tableSchema->columns,$this->child->tableSchema->columns);
        $this->child->refreshMetaData();

        foreach($this->parent->validatorList AS $validator)
            $this->child->validatorList->add($validator);

        foreach($this->parent->metaData->relations AS $name => $relation)
            $this->child->metaData->relations[$name] = $relation;

        foreach($this->parent->attributes AS $attr => $val)
            $this->child->$attr = $val;

        self::$_m = array(get_class($this->parent).'->'.get_class($this->child)=>array('parent'=>$this->parent,'child'=>$this->child));

        return $this->child;
    }
}
