/**
 * Gulp file
 *
 * @author Takuto Yanagida
 * @version 2022-03-23
 */

/* eslint-disable no-undef */
'use strict';

const SRC_JS   = ['src/assets/**/*.js', '!src/assets/**/*.min.js'];
const SRC_PHP  = ['src/**/*.php'];
const SRC_PO   = ['src/languages/**/*.po', '!src/languages/**/wpinc-*.po'];
const SRC_JSON = ['src/languages/**/*.json'];
const DEST     = './dist';

const gulp = require('gulp');

const { makeCopyTask }   = require('./task-copy');
const { makeJsTask }     = require('./task-js');
const { makeLocaleTask } = require('./task-locale');


// -----------------------------------------------------------------------------


const js   = makeJsTask(SRC_JS, DEST, 'src');
const php  = makeCopyTask(SRC_PHP, DEST);
const po   = makeLocaleTask(SRC_PO, DEST, 'src');
const json = makeCopyTask(SRC_JSON, DEST, 'src');

const watch = done => {
	gulp.watch(SRC_JS, js);
	gulp.watch(SRC_PHP, php);
	gulp.watch(SRC_PO, po);
	gulp.watch(SRC_JSON, json);
	done();
};

exports.build   = gulp.parallel(js, php, po, json);
exports.default = gulp.series(exports.build, watch);
