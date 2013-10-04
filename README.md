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
	public $relations = array(
        'hasOne' => array('User','Profile'),
        'hasMany' => array('Posts')
    );

    public function functionName($user_id) {
        return $this->select('User.*,Profile.*')
                        ->from("$this->tablename User")
                        ->join('profile Profile ON Profile.user_id = User.id')
						->left('posts Posts ON Posts.user_id = User.id')
                        ->where('User.id = ?', array($user_id))
                        ->executeModel()
                        ->fetchAll();
    }
}
 ```
 ```php
// NEW UPDATES
User::getInstance()->fields('id,name')->find()->execute()->fetchAll();
 
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

```php 
 // SAMPLE OUTPUT USING EXECUTEMODEL
 
 Array
        (
            [User] => Array
                (
                    [id] => 1
                    [name] => yasir
                    [email] => yasir.mehmood@tset.com
                    [created_at] => 2013-06-11 00:25:23
                )
			[Profile] => Array
				(
					//DATA 
				)
			[Posts] => Array
				(
					[0] => Array
					(
						//DATA 
					)
					[1] => Array
					(
						//DATA 
					)
				)

        )

```