Processing
----------
Processing is a PHP library to asynchronously queue and process background jobs. It uses
redis as its storage engine but provides the necessary extension points for other implementations.

[![Build Status](https://secure.travis-ci.org/mazen/processing.png?branch=master)](http://travis-ci.org/mazen/processing)

Features:

* Written in PHP 5.3
* Can use different storages for its queues
* Supports operation of multiple queues
* Comes with a [Symfony Bundle](https://github.com/mazen/ProcessingBundle)

Usage
-----
example worker:

    $conn     = new \Processing\Connection\RedisConnection();
    $procesor = new \Processing\Processor($conn);

    $procesor->addWorker('filesystem', 'ls', function($directory = '.'){
        echo `ls -al $directory`;
    });
    $procesor->doWork();

example client:

    $conn     = new \Processing\Connection\RedisConnection();
    $procesor = new \Processing\Processor($conn);

    $procesor -> push('filesystem', 'ls', '/home');

License
-------
Copyright (c) 2012 Marcel Beerta

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.