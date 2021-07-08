# Time management system

## System features
* Register and Login with email and password
* Logged in user can create new task with title, comment, date and time spent (in
minutes)
* Logged in user can view paginated table with all theirs tasks
* Logged in user can complete the task and see compleating time of task
* Logged in user can generate and download report file in csv by
date range.
* Report should contain one task per line and total time at the end of table
* If logged in user don't complete the task,this task time caltulating between task date from and current dati time
* Short description in readme file describing how to set up project

## Installation
* Clone git repository with command 
  * ```git clone https://github.com/ivansuzdalev/time_management_system.git ```
* Install sqlite3 driver on your operation system. Example in ubuntu: 
  * ```sudo apt update```  
  * ```sudo apt install sqlite3```
  * Restart you symfony server or you http server


### General folders and files structure in project

```bash
├── Entity
|   ├── Tasks.php
│   └── User.php
├── controllers
|   ├── SecurityController.php
|   ├── SecurityController.php
│   └── SiteController.php
├── services
│   ├── Csv.php
│   └── TasksService.php
├── README.md
├── composer.json
└── .gitignore
```

## Folders and files description

### Services
In this folder place all code from controllers

### Controllers
In this folder is requests getters and visual render code

### Entity
In this folder is data provider from database to controllers
