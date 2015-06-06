## Introduction

FileBox is a small file upload service using [FineUploader](http://fineuploader.com/). 

The server side application is written in PHP.

## Installation

All you need to do is running bower:

```bash
bower update
```

If you don't have bower installed, run:

```bash
npm install -g bower
```

## Features

* Multi-upload
* Drag&Drop
* Chat integration (when a file is uploaded, a message is sent to people connected to the chat) via ChatBox.
* ...

## Configuration

### Client

Don't forget to change the path to the server in `index.html` : change the action attribute `action="path/to/bin/index.php"` in the upload form.

You can decide to enable separate directories according to file format by removing in `js/filebox.js`: 

```js
params: {
  directory: ''
},
```

### Server

Don't forget to change the upload directory in `bin/index.php`
