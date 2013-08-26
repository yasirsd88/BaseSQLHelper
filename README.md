BaseSQLHelper
=============
**BaseSQLHelper ** is a php based class which can be used to perform CRUD operations.

If you're planing to work on core php and you need a database helper class which can perform CRUD operations this class will provide an easy and secure way to perform those operations.

You just need to define four variables in your config file.

1. HOST
2. USERNAME
3. PASSWORD
4. DATABASE

Now you're good to go.


```php
// USAGE.

class ClassName extends BaseDBHelper {

    public $tablename = 'your table name';
    public $primary_key = 'if default primary key is not id mention it here.';
}
 ```
 ```php
// UPDATE
User::getInstance()
		->set(array(
			'id' => 105,
			'name' => "TestName is updated"
		))->save();
//OR 
User::getInstance()->set(array('id' => $id))->update();
//OR 
User::getInstance()->set(array('id' => $id))->update('id = ?',array($id));

// INSERT
User::getInstance()
		->set(array(
			'name' => "TestName is added"
		))
                ->save();

// RETRIEVE USING JOIN
User::getInstance()
		->select('u.*')
		->from('users u')
		->left('users_profile up ON up.user_id = u.id')
		->where('u.id IN (?)', array('1,2'))
		->limit(60)
		->execute()
		->fetchAll();

// RETRIEVE FIRST RECORD
User::getInstance()
		->find('first')
		->execute()
		->fetchFirst();

// DELETE RECORD
User::getInstance()->delete('id = ?', array($id));
// OR 
User::getInstance()->set(array('id' => $id))->delete();
 ```
```php 
 // SAMPLE OUTPUT
 
 Array
(
    [0] => Array
        (
            [User] => Array
                (
                    [id] => 1
                    [name] => yasir
                    [email] => yasir.mehmood@tset.com
                    [created_at] => 2013-06-11 00:25:23
                )

        )

    [1] => Array
        (
            [User] => Array
                (
                    [id] => 2
                    [name] => yasir
                    [email] => yasir.mehmood@tset.com
                    [last_login] => 2013-06-11 00:25:23
                )

        )

)
```