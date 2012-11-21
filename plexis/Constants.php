<?php
/* 
| Plexis CMS versions
| Version consists of 3 seperate version ID's... Major.Minor.Revision
| Whenever an higher version ID is incremented, the lower version ID's reset to 0
|
| CMS_VER_TYPE: CMS Version release type (ie: Alpha, Beta, Release)
| CMS_MAJOR_VER: Major CMS Version
| CMS_MINOR_VER: Minor CMS Version
| MCS_MINOR_REV: Minor Version Revision. This are small updates, not large enough for a minor version update (hotfixes etc)
| CMS_REVISION: Total number of updates made to the cms since the very first commit
| REQ_DB_VER: Required DB version for the cms to operate on
*/

define('CMS_VER_TYPE', 'Pre-Alpha');
define('CMS_MAJOR_VER', 0);
define('CMS_MINOR_VER', 1);
define('CMS_MINOR_REV', 1);
define('CMS_REVISION', 1);
define('REQ_DB_VER', '0.21');
	
// EOF