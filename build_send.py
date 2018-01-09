#!/usr/bin/python
##
## @author Edouard DUPIN
##
## @copyright 2012, Edouard DUPIN, all right reserved
##
## @license APACHE v2.0 (see license file)
##
import urllib, urllib2
import sys
import os
import argparse
import time


parser = argparse.ArgumentParser()
parser.add_argument("-u", "--url",    help="server URL",
                                      default="http://atria-soft.com/ci/build/inject")
parser.add_argument("-r", "--repo",   help="Curent repositoty (generic github name (userName/repoName)",
                                      default="")
parser.add_argument("-s", "--sha1",   help="Sha1 on the commit (git) (256 char limited)",
                                      default="")
parser.add_argument("-b", "--branch", help="branch of the repository (default master)",
                                      default="")
parser.add_argument("-t", "--tag",    help="Tag to register the system 'Linux', 'MacOs', 'Windows', 'IOs', 'Android' ('' for exit)",
                                      default="")
parser.add_argument("-S", "--status", help="Build status 'START', 'OK', 'ERROR' or $?",
                                      default="")
parser.add_argument("-i", "--id",     help="build ID (auto get env variable TRAVIS_BUILD_NUMBER)",
                                      default="")
###################
## Choice 3      ##
###################
parser.add_argument("--test",         help="test value (local server ...)",
                                      action="store_true")
args = parser.parse_args()

if args.status not in ['START', 'OK', 'ERROR']:
	#print("ploppp : '" + str(args.status) + "'")
	if args.status == "0":
		args.status = 'OK'
	else:
		args.status = 'ERROR'

if args.test == True:
	args.url = 'http://atria-soft.com/ci/build/inject'
	args.repo = 'HeeroYui/test'
	args.sha1 = ''
	args.branch = 'master'
	args.tag = 'Windows'
	args.status = 'START'
else:
	if args.tag == "":
		print("[NOTE] (local) not set '--tag' parameter ==> just stop")
		if args.status == 'ERROR':
			print("[NOTE] build error, stop CI ...")
			exit(-3)
		exit(0)
	if args.tag == "linux":
		args.tag = 'Linux';
	if args.tag == "windows":
		args.tag = 'Windows';
	if args.tag == "mac":
		args.tag = 'MacOs';
	list_tag = ['Linux', 'MacOs', 'Windows', 'IOs', 'Android', 'Mingw']
	if args.tag not in list_tag:
		print("[ERROR] (local) set '--tag' parameter: " + str(list_tag))
		exit(-2)
	if args.status == "":
		print("[ERROR] (local) set '--status' parameter")
		exit(-2)

# todo : check if repo is contituated with a "/" ...
# if repo, sha1 and branch is not set, we try to get it with travis global environement variable :
if args.repo == "":
	args.repo = os.environ.get('TRAVIS_REPO_SLUG')
	if args.repo == None:
		args.repo = os.environ.get('CI_PROJECT_NAME')
		if args.repo == None:
			print("[ERROR] (local) missing 'repo' parameter can not get travis env variable")
			exit(-2)
if args.sha1 == "":
	args.sha1 = os.environ.get('TRAVIS_COMMIT')
	if args.sha1 == None:
		args.sha1 = os.environ.get('CI_COMMIT_SHA')
		if args.sha1 == None:
			args.sha1 = ""

if args.branch == "":
	args.branch = os.environ.get('TRAVIS_BRANCH')
	if args.branch == None:
		args.branch = os.environ.get('CI_COMMIT_REF_NAME')
		if args.branch == None:
			args.branch = ""

if args.id == "":
	args.id = os.environ.get('TRAVIS_BUILD_NUMBER')
	if args.id == None:
		args.id = os.environ.get('CI_JOB_ID')
		if args.id == None:
			args.id = ""

print("    url = " + args.url)
print("    repo = " + args.repo)
print("    sha1 = " + args.sha1)
print("    branch = " + args.branch)
print("    tag = " + args.tag)
print("    status = " + args.status)
print("    build id = " + args.id)

data = urllib.urlencode({'REPO':args.repo,
                         'SHA1':args.sha1,
                         'LIB_BRANCH':args.branch,
                         'TAG':args.tag,
                         'STATUS':args.status,
                         'ID':args.id})
# I do this because my server is sometime down and need time to restart (return 408)
send_done = 5
while send_done >= 0:
	send_done = send_done - 1
	try:
		req = urllib2.Request(args.url, data)
		response = urllib2.urlopen(req)
		send_done = -1
	except urllib2.HTTPError:
		print("An error occured (maybe on server or network ... 'urllib2.HTTPError: HTTP Error 408: Request Timeout' ")
	if send_done >= 0:
		time.sleep(5)

#print(response.geturl())
#print(response.info())
return_data = response.read()
print(return_data)
if return_data[:7] == "[ERROR]":
	exit(-1)

if args.status == 'ERROR':
	print("[NOTE] build error, stop travis ...")
	exit(-3)

exit(0)

