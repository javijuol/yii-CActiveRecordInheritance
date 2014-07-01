yii-CActiveRecordInheritance
============================
 
  The CActiveRecordInheritance extension adds up table inheritance to AR models.
 
  To use this extension, just copy this file to your extensions/ directory,
  add 'import' => 'application.extensions.CActiveRecordInheritance', [...] to your
  config/main.php and add this behavior to each child model you would like to
  inherit the new possibilities:
 
       public function behaviors()
       {
           return array( 'CActiveRecordInheritance' => array(
               'class' => 'application.extensions.CActiveRecordInheritance'
           ));
       }
 
 The parent model should have a pk column with an auto-incremental value,
 and the child model should have a pk with another name different from the
 parent and not auto-incremental value.
 
 For example, if we set a parent Car and possible children SportCar and FamilyCar:
 
 Car:
 - id
 - name

 SportCar:
 - car_id
 - power

 FamilyCar:
 - car_id
 - seats

Have to be careful with duplicated names, because this behavior can't
distinguish between ambiguos columns in parent and child models.
The table alias for the parent model will be 'p', and the table alias
for the child model still remains 't'.

Some events have been overwritten by this behavior so it's not possible
to use them in the child model context. Even though, there is a fallback
to mitigate this issue, implementing a 'beforeEvent' method in the child.
The left method is the method overwritted, and the right one is the new
method to implement in the child models if they are needed.
 - afterConstruct => beforeAfterConstruct
 - beforeFind     => beforeBeforeFind
 - afterFind      => beforeAfterFind
 - beforeSave     => beforeBeforeSave
They will be executed just before the 'behavior overwriting method', and
the MUST NOT return anything, as regular event should return parent::event
to avoid framework workflow problems.

TODO list:
 - makes a delete cascade functionality
