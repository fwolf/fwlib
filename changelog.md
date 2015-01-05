#ChangeLog



### Un-versioned



### v2.3.4 (2015-01-05)

- Move class files to src/ directory
- Move testcase files to tests/ directory
- Remove @author and @since tag
- Mvc: New UrlGenerator class and interface
- Mvc: Rename Controler to Controller
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
- Add Badge Poser from pugx.org



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

- Remove @package tag and useless class description in testcase
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
- Mvc\AbstractControler: Define module and action parameter name as property



## v2.0 (2014-01-08)

- Follow to PSR-0,1,2,4 standard, all class/function rewrited
- Use PHPUnit for unit test
- All class put in Fwlib/ sub-directory
- Most class are optimized
- Old function and class are kept
- Need PHP 5.3.0+



## v1.0 (2013-07-17)

All history from 2003, no changelog info, check Git log if needed.
