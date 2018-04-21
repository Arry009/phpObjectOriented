# phpObjectOriented
PHP面向对象-看父类调用子类方法
原文  http://www.cnblogs.com/skyfynn/p/8878393.html
主题 面向对象编程 PHP
大部分面向对象编程语言中，父类是不允许调用子类的方法的，但是PHP中可以

1、父类调用子类方法示例

class A
{
    public function testa()
    {
        $this->testb();
    }
}
class B extends A
{
    //仅对public方法可以进行父类调用子类
    public function testb()
    {
        echo 'bbbbb';
    }
}

$b = new B();
$b->a();
//输出bbbbb
2、弊端

尽量避免这么写，这样的设计非常不好，如果需要写这样的代码，那么一定有其他的设计模式可以取代它

3、好的设计方法

首先发现父类调用子类这种方式是在Yii2.0中的save方法，这个方法位于\yii\db\BaseActiveRecord中，save方法中调用了insert或者update方法，然而在当前类中并没有找到这两个方法，因为BaseActiveRecord实现了ActiveRecordInterface接口，所以应该是必须要实现这两个方法的，但这两个方法却出现在了他的子类\yii\db\ActiveRecord中，也就是子类实现了

两个知识点：

父类可以调用子类的方法
如果一个抽象类实现了一个接口，那么它可以不实现接口中方法、而由子类去实现
这样的好处是：如果接口中新增了方法、并且所有的子类都是需要做相同的实现，那么就可以直接将实现放在这个抽象类中，否则对应的各个子类去各自实现

//yii2.0
interface ActiveRecordInterface
{
    public function update($runValidation = true, $attributeNames = null);
    public function insert($runValidation = true, $attributes = null);
}

//BaseActiveRecord实现了ActiveRecordInterface接口
abstract class BaseActiveRecord extends Model implements ActiveRecordInterface
{
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->getIsNewRecord()) {
            return $this->insert($runValidation, $attributeNames);
        } else {
            return $this->update($runValidation, $attributeNames) !== false;
        }
    }
}

//ActiveRecord
class ActiveRecord extends BaseActiveRecord
{
    public function insert($runValidation = true, $attributes = null)
    {
        //具体实现
    }
    public function update($runValidation = true, $attributeNames = null)
    {
        //具体实现
    }
}
4、应用

如最近的需求：新建了语文、数学、英语三科错题数据库表，目前结构表结构基本一致，但未来可能不同的错题可能会有不同的差异，所以分开三个库建了三张表

对于目前来说三科都需要查询错题数量统计，这个时候一种做法是三个类分别去实现这个方法，另外一种方式就是可以定义一个接口，接口中定义统计的方法、目前来说并无差异，所以为三个错题的类建立一个抽象的基类，并将统计错题的方法在抽象基类中实现，如果以后出现了不同的统计方式，子类可以直接重写这个统计方法

如果表发生了改变，统计方法发生了改变，查询依赖于对应科目表中的不同字段，也就是具体数学|语文|英语错题中的变量、常量、或方法，就可以直接在接口中定义、在子类中实现、在抽象基类中调用子类的这个方法

//实例 定义一个接口 里面包含计算错题统计的方法
interface WrongNoteInterface 
{
    public function statistics();
    //public function select();
    //public function where();
}
//抽象基类
abstract class WrongNoteBase implements WrongNoteInterface
{
    public function statistics()
    {
        $select = 'xx’;// $select = $this->select();
        $where  = ‘xx’;// $where  = $this->where();
        return $this->find()->select($select)->where($where)->asArray()->all();
    }
}
//语数外继承基类
class ChineseWrongNote extends WrongNoteBase
{
    const IS_RIGHT_0 = 0;
    const IS_RIGHT_1 = 1;
}
像上面的这种情况，如果要查的字段select变了，select可能是个SUM(xxx)这中结构，而其中计算SUM的条件可能不一样，这个时候就可以在接口中添加:

public function select();
public function where(); 
然后在修改statistics中$select获取方式变为直接调用子类方法select，代码改为注释中的内容
总结：经过这样的修改，避免了代码的冗余（一个统计代码复制三份）、可以方便的对类进行扩展
