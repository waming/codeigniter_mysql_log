# codeigniter
一个记录CI中需要记录所有SQL语句到日志中的方法。
# 使用方法
1、需要建立一个自己的my_model继承系统中的CI_Model
2、需要有一个自己的model层
3、详情见代码，重点就是在my_Model中的析构方法中加入了代码
# 传统的hook
hook方法貌似没效果，可能是我重新封装了$this->db.
