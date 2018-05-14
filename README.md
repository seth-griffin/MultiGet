# Multi-Get Example Using PHP
-----

## Overview

### Workspace contents

```
.
├── App
│   ├── App.php                         Main app class file
│   ├── Http
│   │   └── FileDownloader.php          FileDownloader class file
│   ├── IO                          
│   │   └── FileWriter.php              FileWriter class file
│   └── multiGet.php                    Final solution cli wrapper
├── README.md                           This readme file
└── spike                               A spike solution. Execute on the terminal with ./spike after using chmod to make it executable
```

### Design Decisions

Starting off I wanted to spike the problem. I started with a very quick and dirty CLI script that meet the following criteria:

1. Source URL should be specified with a required command-line option
2. File is downloaded in 4 chunks (4 requests made to the server)
3. Only the first 4 MiB of the file should be downloaded from the server
4. Output file may be specified with a command-line option (but may have a default if not set)
5. No corruption in file - correct order, correct size, correct bytes
6. File is retrieved with GET requests

I wanted to use CURL to start with since it's very simple and a tool that I am familiar with. This introduces a dependency at the OS level so be sure to install php-curl prior to trying to run the spike.

After I was done writing the spike I decided to create an object oriented version of the spike solution including some better error handling.

### Usage

Install php-curl if it isn't included on your system

Run the spike using:
```
./spike --url=url [--outputFileName=outputfilename]

```

Run the application using:

```
php multiGet.php --url=url [--outputFileName=outputfilename]
```

You'll have to switch to the working directory (App) prior to running the above