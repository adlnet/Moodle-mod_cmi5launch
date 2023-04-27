cmi5launch
============

A plug in for Moodle that allows the launch of cmi5 content which is then played in a cmi5 player and tracked to a separate LRS. 

## What you will need

To use this plugin you will need the following:

* Moodle 4.0+
* Login details for the admin account 
* A Moodle course setup where you would like to add the activity
* A piece of cmi5 compliant e-learning that also implements the launch mechanism, for example e-learning produced using Articulate Storyline or Adobe Captivate and packaged as a .zip file
* A cmi5 compliant LRS
* A running instance of the cmi5 player (https://github.com/adlnet/CATAPULT/tree/main/player)
* A copy of this plugin

## Notes

When installing from github, wrap this entire repository in a zip file named cmi5launch.zip

# Flowchart

This flowchart shows the path a user takes to get to a cmi5 Lesson Link. Once the link is clicked, the cmi5 Player opens in a new tab or window. The Moodle Application negotiates the connection by supplying credentials, tenant, and the current user session information. The Lesson Link contains a token in which both sides can track the user.

```mermaid
flowchart TB
  subgraph MOODLE[Moodle Application]
    direction TB
    Course(Course) --> Assignment(Assignment) --> Activity --> Link
    Webhook[Progress\nEndpoint]
    subgraph Activity
      Link(Lesson Link)
    end
  end
  MOODLE -.-> MoodleDB[(Moodle DB)]

  subgraph cmi5[cmi5 Player]
    direction TB
    Lesson --> Lesson
  end

  cmi5 -.-> Webhook
  Link <---> cmi5
  Link -.-> LRS[(LRS)]
  cmi5 -.-> cmi5DB[(cmi5 DB)]
  cmi5 -.-> LRS
```
## Sequence diagrams for connecting to CMI5 player

Following are the two functions Moodle uses to create a course and retrieve a course URL from the CMI5 player.

### Create course

```mermaid
sequenceDiagram
    title: Create Course
    
    participant Moodle
    participant CMI5
    participant C-DB as CMI5's MySQL Database
    participant M-DB as Moodle's MySQL Database
   
    
    Moodle->>+CMI5: Send POST to /api/v1/course
    break error
    CMI5-->>+Moodle: response other than 200
    Note over Moodle, CMI5: Check content-type, package, or token
    end
    CMI5->>+C-DB: Create course in DB
    CMI5->>+Moodle: 200, returns JSON body
    Moodle->>+M-DB: Save lmsId, Id, course metadata

```
### Retrieve launch URL

```mermaid
sequenceDiagram
    title: Retrieve URL
    
    participant Moodle
    participant CMI5
    participant M-DB as Moodle's MySQL Database
   
    
    Moodle->>+CMI5: Send POST to /api/v1/course/{courseId}/launchurl/{AU index}
    break error
    CMI5-->>+Moodle: response other than 200
    Note over Moodle, CMI5: Check actor account info, returnurl, token
    end
    
    CMI5->>+Moodle: 200, returns JSON body with session id, launch method, and launch url
    Moodle->>+M-DB: Save returned course info to DB

```

## User progress

The cmi5 player tracks user progress, however the Moodle application will also want to track progress. Moodle will present the progress details to the user to let them know whether the lesson was completed, or how far along the user is in the lesson. This is not implemented yet.

### Introduce a webhook

Moodle and cmi5 Player do not currently share a user's progress. One option is for the cmi5 player to ping Moodle via a webhook whenever progress and/or completion has been made.

Webhooks are "user-defined HTTP callbacks". They are usually triggered by some event, such as pushing code to a repository or a comment being posted to a blog. When that event occurs, the source site makes an HTTP request to the URL configured for the webhook. Users can configure them to cause events on one site to invoke behavior on another.

One consideration is that the cmi5 Player will also need private credentials to request a progress update and/or completion.
