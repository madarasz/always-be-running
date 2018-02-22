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

- Conclude by CSV

*concluding is also tested in **"008 Claiming"***

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

##### Claiming, no top-cut, NRTM import, conflicts
* Login with NRDB (regular user)
* Navigate to Organize page, create from results
* Fill out form with multi-day, concluded, online tournament data
* Save tournament
* **Add claim with published decklists**
  * Validate tournament details page, validate claim
  * Import nrtm results (conflicting), validate conflicts
  * Remove claim, remove imported entries
* **Add claim with IDs**
  * Validate tournament details page, validate claim
  * Import nrtm results (conflicting), validate conflicts
  * Remove claim, remove imported entries
* **Add claim with other user's deck**
  * Validate tournament details page, validate claim
  * Import NRTM results (conflicting), validate conflicts
* Go to organize, validate conflict and match data icons
* Go to tournament details, remove claim, validate tournament page, conflict is gone
* **Add Claim with published decks**, validate conflict
  * Remove claim
* **Add claim with IDs**, validate conflict
  * Remove claim
* **Add claim with other user's deck**, validate conflict
  * Remove conflicting imported entry, validate conflict is gone
* Go to Personal page, validate tournament entry with claimed status

##### Claiming, import (Cobr.ai), top-cut, no conflicts
* Login with NRDB (regular user)
* Navigate to Organize page, create from results
* Fill out form with multi-day, concluded, online tournament data
* Save tournament
* **Claim with published decklists**
  * Validate tournament details page, validate claim
  * Import Cobra results (no-conflict), validate absence of conflicts
  * Remove user claim, remove imported claims
* **Claim with IDs**
  * Import Cobra results (no-conflict), validate absence of conflicts
  * Remove user claim, remove imported claims
* **Add claim with other user's deck**
  * Import Cobra results (no-conflict), validate absence of conflicts
  * Remove user claim, remove imported claims
* Import Cobra results, **add claim with published decklist**, validate absence of conflict
  * Remove user claim, remove imported claims
* Import Cobra results, **add claim with IDs**, validate absence of conflict
  * Remove user claim, remove imported claims
* Import Cobra results, **add claim with other user's deck**, validate absence of conflict
     
##### Claiming, import (Cobr.ai), top-cut, conflicts
* Login with NRDB (regular user)
* Navigate to Organize page, create from results
* Fill out form with multi-day, concluded, online tournament data
* Save tournament
* **Add claim with published decklists**
  * Import Cobra results, validate claim and conflict in swiss
  * Remove user claim, remove imported claims
* **Add claim with IDs**
  * Import Cobra results, validate claim and conflict in swiss
  * Remove user claim, remove imported claims
* **Add claim with other user's deck**
  * Import Cobra results, validate claim and conflict in swiss
  * Remove user claim, remove imported claims
* Import Cobra results 
* Validate imported entries and points
* **Add claim with published decklists**, validate conflict in top
  * Remove user claim
* **Add claim with IDs**, validate conflict in top cut
  * Remove user claim
* **Add claim with other user's decks**, validate conflict in top
  * Remove user claim
* **Add claim with published deck, no conflict**
  * Validate tournament details, user's claim, the absence of conflict
  * Remove user claim, remove imported claims
  * Import Cobra results
* **Add claim with IDs, no conflict**
  * Validate tournament details, user's claim, the absence of conflict
  * Remove user claim, remove imported claims
  * Import Cobra results
* **Add claim with other user's decks**
  * Validate tournament details, user's claim, the absence of conflict
  
##### Claiming with user without decks
* Login with NRDB (user without decks)
* Navigate to Organize page, create from results
* Fill out form with concluded, online tournament data
* Save tournament
* Open claim modal, validate modal
* Add other user's runner deck ID, validate modal
* Add other user's corp deck ID, validate modal
* Clear deck IDs, validate modal
* Add deck IDs again, validate modal, submit claim
* Validate tournament page and claim

**TODO**
- Claiming with private deck
- Manual import
- Admin: delete other user's claim
- Check winner is topcut-winner, not swiss winner in Results table and Featured box
- relaxed tournaments


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

- click tournament on Results page