BaseSQLHelper
=============

// USAGE.

class ClassName extends BaseDBHelper {

    public $tablename = 'your table name';

}

// UPDATE
User::getInstance()
		->set(array(
			'id' => 105,
			'name' => "TestType is updated"
		))->save();
//OR 
User::getInstance()->set(array('id' => $id))->update();
//OR 
User::getInstance()->set(array('id' => $id))->update('id = ?',array($id));

// INSERT
User::getInstance()
		->set(array(
			'name' => "TestType is updated"
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
