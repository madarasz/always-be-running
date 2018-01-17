## Test cases

### 001 Login

##### Login / logout
- navigate to Results page, results table is visible, to-be-concluded tab is not visible
- try Personal page, check login required
- check Profile page, validate page
- try Admin page, check 403
- check Videos page, validate page
- navigate to Organize page, requires login
- login with NRDB (regular user)
- check Organize page, create tournament option available, Profile, Personal menus available
- navigate to Results page, results table is visible, to-be-concluded exists
- navigate to Personal page, validate page
- check Profile page, validate page
- try Admin page, check 403
- check Videos page, validate page
- navigate to Organize page, logout
- check Organize page, requires login, login menu available
- **TODO:**
   - *tournament detail page while logged out*

### 002 Create event

##### Create single day tournament (future date)
- Navigate to Organize page
- Login with NRDB (regular user)
- Validate login, click Create Tournament
- Validate tournament form, fill out form with single day tournament data
- Validate that location is found and correct
- Save tournament, validate tournament details page
- Click Update button, verify tournament form, click Cancel
- Navigate to Organize page, validate entry on table of created tournaments
- Navigate to Upcoming page, check upcoming tournaments table
- **TODO**: *Validate tournament on profile page*

##### Create recurring event
- Navigate to Organize page
- Login with NRDB (regular user)
- Validate login, click Create Tournament
- Validate tournament form, fill out form with recurring tournament data
- Validate that location is found and correct
- Save tournament, validate tournament details page
- Click Update button, verify tournament form, click Cancel
- Navigate to Organize page, validate entry on table of created tournaments
- Navigate to Upcoming page, check recurring tournaments table

##### Create online, multi-day, concluded tournament
- Navigate to Organize page
- Login with NRDB (regular user)
- Validate login, click Create Tournament
- Validate tournament form, fill out form with multi-day, concluded, online tournament data
- Save tournament, validate tournament details page
- Click Update button, verify tournament form, click Cancel
- Navigate to Organize page, validate entry on table of created tournaments
- Navigate to Results page, check results table

##### Tournament form validation
- Navigate to Organize pag
- Login with NRDB (regular user)
- Validate login, click Create Tournament
- Fill date, end date (earlier than start date), submit, check for validation errors
- Fix end date > date, set conclusion, submit, check for validation errors
- Fix end date > date, set conclusion, submit, wrong player number, check for validation errors

### 003 Import events

##### Import from NRTM.json (no top-cut)
- Navigate to Organize page
- Login with NRDB (regular user)
- Validate login, click Create from Result
- Validate Conclude modal, upload NRTM.json
- Validate imported form values, fill remaining fields, create tournament
- Validate tournament details page with results
- Validate matches information and points
- Verify concluded tournament on Results page
- Verify concluded tournament on Organize page

**TODO**
- Import from Facebook

### 006 Concluding tournament

##### Manual conclude
- Navigate to Organize page
- Login with NRDB (regular user)
- Validate login, click Create Tournament
- Fill out tournament form with past tournament data
- Save tournament, validate tournament details page
- Navigate to Results page
- Check that tournament is in to-be-concluded table and not in results table
- Navigate to tournament details
- Conclude tournament manually, assert tournament page
- Navigate to Results page, check that tournament is in results table, not in to-be-concluded
- Navigate to tournament view, revert conclusion, validate tournament
- Navigate to Results page
- Check that tournament is in to-be-concluded table and not in results table

**TODO**

- Conclude by NRTM
- Conclude by CSV
- with top-cut / without top-cut

### 008 Claiming

##### Registering, un-registering
* Navigate to Organize page
* Login with NRDB (regular user)
* Validate login, click Create Tournament
* Fill out tournament form with past tournament data
* Save tournament, validate tournament details page
* Click register, validate tournament page
* Go to Personal page, validate registered tournament entry
* Go back to tournament details page, unregister, validate tournament details page
* Register again, conclude tournament, validate tournament details page
* Go to Personal page, validate claim button
* Click unregister on Personal page, validate tournament missing on Personal page

##### Claiming with published decks
* Login with NRDB (regular user)
* Navigate to Organize page, create from results
* Fill out form with multi-day, concluded, online tournament data
* Save tournament
* Click claim, validate claim modal, add claim of published decklists
* Validate tournament details page, validate claim
* Import NRTM results (conflicting), validate conflicts
* Go to organize, validate conflict and match data icons
* Go to tournament details, remove claim, validate tournament page, conflict is gone
* Claim again, validate conflict
* Remove conflicting imported entry, validate conflict is gone
* Go to Personal page, validate tournament entry with claimed status

**TODO**
- Claiming with other's deck
- Claiming with private deck
- Claiming with IDs
- Manual import
- Merges (import + user claim)
   - user claim then NRTM import
   - NRTM import then user claim
- Delete own
- Delete all manually imported


----------

**TODO**

### 004 Edit tournament

### 005 Upcoming page
##### Filters
##### User's default country
### 007 Results page
##### Results table
##### Waiting for conclusion table
##### Filtering
### 009 Photos
##### Adding photos
##### Deleting photos
##### Rotating photos
### 010 Videos
##### Adding Youtube video
##### Adding Twitch video
##### Tagging users in videos
##### Untagging users in videos
##### Deleting videos
##### Videos page
### 011 Personal page
### 012 Profile page
##### Badges
### 013 Admin
##### Approve tournament
##### Reject tournament
##### Approve photo / photos
##### Featuring upcoming tournament
##### Featuring concluded tournament
### 014 Permissions
- other user cannot delete tournament
- other user cannot delete user claim
- other user cannot delete imported entry
- user cannot hard delete tournament
- other user cannot edit tournament
- browsing as a non-user visitor
- access denied: create tournament form, admin page
- logged out: cannot conclude, cannot see tournaments to be concluded

+ click tournament on Results page