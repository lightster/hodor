# 0.2.0 release notes

Breaking Changes:

 - Upgraded Hodor's dependency on lightster/yo-pdo to v0.1.3+ from v0.0.2+
 - Deprecated \Hodor\JobQueueFacade and will remove in 0.3.0

Bug Fixes:

 - Stop attempting to mark job as failed if it has already been marked as successful
   ([#213](https://github.com/lightster/hodor/pull/213))

Other Changes:

 - Refactored internals on a massive scale
 - Increased testing coverage
 - Improved PHPdoc
