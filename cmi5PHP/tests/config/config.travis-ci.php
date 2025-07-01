<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

$lrss = [
    [
        'endpoint' => 'https://cloud.scorm.com/tc/0CKX3A0SF2/sandbox/',
        'username' => 'PjRb2iE9WsUSso_UYCE',
        'password' => '3qoocGjKnfoYrtJhPrU',
        'version'  => '1.0.1',
    ],
];
$keys = [
    'public'   => getenv('TRAVIS_BUILD_DIR') . '/tests/keys/travis/cacert.pem',
    'private'  => getenv('TRAVIS_BUILD_DIR') . '/tests/keys/travis/privkey.pem',
    'password' => 'travis',
];
