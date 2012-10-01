NoSql Datasource for CakePHP
===========================================

This class provides an abstraction layer for your nosql datasources.

###What it is not####

* This is not a full featured CakePHP abstraction layer, as you can't call `$model->save()` nor `$model->find()`.  
Reason is that some nosql database, such as Redis, don't provide a single structure for saving datas (redis can save in a list, a hashset, a set), unlike sql database (tables with columns).  
**Benefits:** More control on how to save the datas, thus more performance
* This datasource is not linked to a model. You don't access it via `$model->whaterverCommand()`.  
Reason is that this datasource doesn't behave like the DboDatasource, but more like the Cache.  
**Benefits:** You can search the database from your views (woah! It's not mvc …).

###Available noSql Datasource###

For now, only [Redis](http://redis.io/) is available.

###How to Use###

Database call is static, and behave like the Cache. Each command will depend on the available command for your specific nosql datasource. For Redis, available commands are `NoSql::Redis()->xxx()`, where xxx are all the [generic command available for redis](http://redis.io/commands).

In fact, instead of doing that :
		
	$Redis = new Redis()
	$Redis->pconnect();
	$result = $Redis->hget('myFirstKey');
	
You do that :

	$result = NoSql::Redis()->hget('myFirstKey');
	
And that command is available anywhere, even in the views. Channeling all commands through the NoSql class enables queries logging, that you can see in DebugKit.

###Use case####

Example will use Redis.
Main selling points of redis are its speed (as fast as memcached), persistent datas (memcached datas are flushed after system reboot), and low-level datas structure (set, hashset, etc …).

It can be used like a persistent mvc datasource (attached to a model), or a simple cache.

Let's say we have a posts database, a comments database, an users database.  
A Post has many Comment, and a Comment hasOne User.  
You can get a post comments and its user by finding a post, and uses `contain` to fetch its comments and user.

	$post = $this->Post->find('all', array(
		'conditions' => array('id' => 256), 
		'contain' => array('Comment' => array('User))
	));
	
The MYSQL JOIN here are costly, it joins 3 tables. We can reduce that to 2 by not joining the User, since we already have the user_id in the Comment tables (because of the HasOne relations, each comment already has a user_id field). Joining the user will only retrieve the username.

Then, in the views, we just call `NoSql::Redis()->get('User:' . $post['Comment][0]['user_id])` to retrieve the username of the user for the specified comment. Of course, you have to prepoluate the databases with the users' username first. And datas will persist, since it's not a cache, but a database.

This is just a very basic example, but you can store entire object in Redis (via hashset), without the serialize/unserialize overhead you encounter with memcached.

###Install###

* [Download](https://github.com/kamisama/DebugKitEx/zipball/master) and drop the *NoSql* folder in your *Vendor* directory.  
Or you can clone it directly with  

		git clone git://github.com/kamisama/CakePHP-NoSQL-Datasource.git yourapp/Vendor/NoSql
* Import it in your application, by putting the following at the beginning of your *AppModel.php* :
	
		App::import('Vendor', 'NoSql/NoSql');
		
* Start using by calling `NoSql::DATASOURCE()->COMMAND()`. DATASOURCE is the name of the NoSql datasource you want to use, and COMMAND is the datasource own command. Only datasource available now is Redis.

###View queries in DebugKit###

Main reason I write this layer, instead of using the Redis class direcly, is logging. I wanted to log all nosql queries, and calling :

	$Redis = new Redis()
	$Redis->pconnect();
	$result = $Redis->hget('myFirstKey');
	
doesn't let me do that. I you want to view all your nosql queries, see my other plugin, [DebugKitEx](https://github.com/kamisama/DebugKitEx), and extension to the original [debugkit](https://github.com/cakephp/debug_kit) plugin, providing an additional panel for all the nosql queries.

##Changelog

###**v.0.5.1** [2012-10-01] 

* [fix] Fix `getLogs()` that was always returning null

###**v.0.5** [2012-10-01] 

* [new] Get logs from all datasources with `Nosql::getLogs()`

###**v.0.4.1** [2012-09-18] 

* [fix] Remove DataSourceNotFoundException class, already defined in Cake core

###**v.0.4** [2012-09-09] 

* [new] Add query time in logs
* [fix] Restructure folder tree to facilitate git cloning


###**v.0.3** [2012-08-30] 

* [change] Code formatting

###**v.0.2** [2012-07-02] 

* [new] Fallback to Redisent when PHPRedis is not installed
