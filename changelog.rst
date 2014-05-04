..	-*- mode: rst -*-
..	-*- coding: utf-8 -*-


===========================================================================
ChangeLog
===========================================================================



v2.3    2014-05-04
====================

- Rewrite Db\DbDiff, to namespace Db\Diff, separated to several classes
- Rewrite Workflow, separated to manager, model and view class
- All demos has been moved to demo/ directory
- All benchmarks has been moved to benchmark/ directory
- Db: Generate rollback SQL in reverse order of commit
- Db: New class AbstractSequence
- Html: Display ListTable data by order of title keys
- Mvc: Remove dependence of ServiceContainer
- Mvc: Allow customize output parts combine order



v2.2    2014-01-23
====================

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



v2.1    2014-01-12
====================

- New ServiceContainerInterface
- New Model AbstractWorkflow and AbstractWorkflowView
- Mvc\AbstractView: New property $methodPrefix
- Mvc\AbstractControler: Define module and action parameter name as property



v2.0    2014-01-08
====================

- Follow to PSR-0,1,2,4 standard, all class/function rewrited
- Use PHPUnit for unit test
- All class put in Fwlib/ sub-directory
- Most class are optimized
- Old function and class are kept
- Need PHP 5.3.0+



v1.0    2013-07-17
====================

All history from 2003, no changelog info, check Git log if needed.
