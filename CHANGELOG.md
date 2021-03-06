# Fwlib ChangeLog



## v2.4 (2015-10-28)

WARNING: Big update, break backward compatibility!

- Require PHP 5.5+
- Move Bridge/PHPMailer out to https://packagist.org/packages/fwolf/phpmailer-wrapper
- Move Bridge/PHPUnitTestCase out to https://packagist.org/packages/fwolf/phpunit-wrapper
- Require PHPUnit as dev dependence
- Require vfsStream as dev dependence
- Require PHP-Mock as dev dependence


Many class rewritten with new feature of PHP 5.5 and better OOP structure.


- Change some abstract class to trait
- Base: New SingleInstanceTrait
- Base: New ServiceContainerTrait
- Base: New class ServiceContainer
- Base: New ServiceContainerAwareTrait
- Base: Remove ServiceContainerAwareInterface
- Base: Remove class AbstractServiceContainerAware
- Base: Delete class AbstractAutoNewConfig
- Base: Delete class AbstractAutoNewInstance
- Base: Delete class AbstractServiceContainer
- Auth: Rename AbstractAccessControl to AccessControllerTrait
- Auth: New SessionHandlerInterface and handlers
- Auth: New SessionHandlerAwareTrait
- Cache: Rename CacheInterface to HandlerInterface
- Cache: New Logger class and interface
- Cache: New LoggerAwareTrait
- Cache: New HandlerTrait
- Cache: New AbstractHandler and handlers
- Cache: New HandlerAwareTrait
- Config: New ConfigAwareTrait
- Config: New ConfigAwareInterface
- Config: New StringOptionsAwareTrait
- Db: New AdodbAwareTrait
- Db: Delete class AbstractDbClient
- Html: Rewrite ListTable and rename to ListView
- Html: Rewrite element define and implement classes
- Html: Rewrite Form generator classes
- Html: New ClassAndIdPropertyTrait
- Html: New ElementPropertyTrait
- Html: New AbstractElement class
- Util: New UtilContainerAwareTrait
- Util: Delete class AbstractUtilAware and interface
- Util: New uuid generator Base36Short, based on Base36
- Util: New ObjectUtil
- Util: Move uuid generators to Util/Uuid/
- Util: Move Rfc2047, McryptSimpleIv to Util/Algorithm/
- Util: Move common utils to Util/Common/
- Web: Rename Mvc/ to Web/
- Web: New Request class, trait and interface
- Web: New RequestAwareTrait
- Web: New Response class, trait and interface
- Web: New ResponseAwareTrait
- Web: New class HtmlHelper
- Web: New HtmlHelperAwareTrait
- Web: New GetControllerClassByNamespaceTrait
- Web: New GetViewClassByNamespaceTrait
- Web: Rename AbstractViewCache to AbstractViewWithCache
- Validator: Use StringOptions in constraint
- Validator: New Rule class


- Use short array syntax
- Use class keyword to reference full qualified class name
- demo: Move example of ClassLoader to demo/
- demo: Move curl demo from tests/ to demo/
- demo: Use httpbin.org for Curl test
- demo: New demo of ListView
- Util: Reconstruct HttpUtil with filter functions
- Test: Provide TestServiceContainer to use in test cases
- Test: New TestServiceContainerAwareTrait
- Test: New CheckCalledTrait



### v2.3.5 (2015-02-25)

This is last version for PHP 5.3.

- Cache: New class CachedCaller and interface
- Cache: New interface CachedCallerAwareInterface
- Mvc: Migrate class AbstractModel to Cache\AbstractCachedCallerAware
- Cache: Add read/write renderer feature
- Cache: Do not rewrite cache when read successful
- Config: Split checkServerId() from limitServerId()
- Util: New method ArrayUtil::pick(), with key replace mode
- Util: Add pick method to http GET/POST
- Util: Add get all method to http GET/POST
- Mvc: Add method getLink() and getFullLink()
- Util: Move benchmark code out of test



### v2.3.4 (2015-01-05)

- Move class files to src/ directory
- Move test case files to tests/ directory
- Remove @author and @since tag
- Mvc: New UrlGenerator class and interface
- Mvc: Rename Controller to Controller
- Util: Add individual getter method for each util
- Net: Assign TLSv1 cipher to use for SSL
- Workflow: Method getViewAction() can call without workflow action
- Change changelog to markdown format



### v2.3.3 (2014-11-14)

- Change to PSR-4 autoload style
- Util: Remove method evalWithTag()
- Cache: Ignore test of weight
- Bridge: Enable $bulkBind option by default
- js: Find puppet with id=name as failsafe
- js: Use puppet as visual element to show validate info
- css: Add class .yes and .no
- Use Travis CI for continuous integration test
- Add badge from SensioLabsInsight code analysis
- Add Badge Poser from https://poser.pugx.org/



### v2.3.2 (2014-09-01)

- Util: Add cookie and session relate methods
- Util: Add method getBirthday()
- Util: Add method getGender()
- css: New class .bad and .good
- Cache: Only set json serializer option when json is enabled
- Workflow: Split workflow action parameter out from router view action
- Html: Allow set multiple class to ListTable
- css: Make class .error covers html tag p, td, th, li
- Html: Rename method setOrderby() to setOrderBy()
- Html: Rename config orderbyXxx to orderByXxx
- Html: Remove dependence of AbstractAutoNewConfig
- Html: Use BEM CSS naming convention
- Html: Page param p0, p1 will be simplify to p
- Html: Fix array_walk() with addslashes() cause warning



### v2.3.1 (2014-05-13)

- Workflow: Add limit and disable/enable actions feature
- Db: Rename delErrorSql() to deleteErrorSql()
- Html: Remove dependence of AbstractAutoNewConfig
- demo: Enable frontend validate by default



## v2.3 (2014-05-04)

- Rewrite Db\DbDiff, to namespace Db\Diff, separated to several classes
- Rewrite Workflow, separated to manager, model and view class
- All demos has been moved to demo/ directory
- All benchmarks has been moved to benchmark/ directory
- Db: Generate rollback SQL in reverse order of commit
- Db: New class AbstractSequence
- Html: Display ListTable data by order of title keys
- Mvc: Remove dependence of ServiceContainer
- Mvc: Allow customize output parts combine order



## v2.2 (2014-01-23)

- Remove @package tag and useless class description in test case
- Try to avoid using eval()
- Add some getter method
- Use full word in naming
- Auth: New interface and abstract class of AccessControl
- Auth: New interface and abstract class of Authentication
- Auth: New interface and abstract class of UserSession
- Bridge\Adodb: Rename method getByPk() to getByKey()
- Bridge\PHPMailer: Set $Sender when call setFrom()
- Config: Move method limitServerId() to class GlobalConfig
- Db\CodeDictionary: Add method getMultiple() to simplify get()
- Db\CodeDictionary: Allow easy initialize by assign value when define $dict
- Db\CodeDictionary: Only allow single primary key column
- Db\CodeDictionary: Remove dependence of Base\AbstractAutoNewConfig



## v2.1 (2014-01-12)

- New ServiceContainerInterface
- New Model AbstractWorkflow and AbstractWorkflowView
- Mvc\AbstractView: New property $methodPrefix
- Mvc\AbstractController: Define module and action parameter name as property



## v2.0 (2014-01-08)

- Follow to PSR-0,1,2,4 standard, all class/function rewritten
- Use PHPUnit for unit test
- All class put in Fwlib/ sub-directory
- Most class are optimized
- Old function and class are kept
- Need PHP 5.3.0+



## v1.0 (2013-07-17)

All history from 2003, no changelog info, check Git log if needed.
