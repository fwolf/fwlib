
This directory stores base classes used for test purpose. We did not use the
name Helper or Assistant to avoid conflict with working classes.

For IDE to correct hint import of these helper class, this directory need to
mark as 'Sources' while its parent directory is marked as 'Tests'. In PHPStorm,
if not done this, the auto import works, but the import(use) statement are
always shown dark as unused import.
