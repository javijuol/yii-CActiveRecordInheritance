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
