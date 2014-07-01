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
 
 
  ========= BEGIN EXAMPLE ==========
  class Car extends CActiveRecord {
   // property integer $id
   // property string  $name
       ...
  }
 
  class SportCar extends Car {
   // property integer $car_id
   // property number  $power
       ...
 
       public function behaviors()
       {
           return array( 'CActiveRecordInheritance' => array(
               'class' => 'application.extensions.CActiveRecordInheritance'
           ));
       }
  }
 
  class FamilyCar extends Car {
   // property integer $car_id
   // property integer $seats
       ...
 
       public function behaviors()
       {
           return array( 'CActiveRecordInheritance' => array(
               'class' => 'application.extensions.CActiveRecordInheritance'
           ));
       }
  }
 
 
  $sportCar = new SportCar();
  $sportCar->name  = 'FastCar';
  $sportCar->power = 120;
  $sportCar->save();
 
  $familyCars = FamilyCar::model()->findAllByAttributes(array('seats'=>8));
 
  ========= END EXAMPLE ==========
