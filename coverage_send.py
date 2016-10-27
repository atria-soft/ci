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
                                      default="http://atria-soft.com/ci/coverage/inject")
parser.add_argument("-r", "--repo",   help="Curent repositoty (generic github name (userName/repoName)",
                                      default="")
parser.add_argument("-s", "--sha1",   help="Sha1 on the commit (git) (256 char limited)",
                                      default="")
parser.add_argument("-b", "--branch", help="branch of the repository (default master)",
                                      default="")
###################
## Choice 1      ##
###################
parser.add_argument("-j", "--json",   help="all data to send ... (json file NOT json data)",
                                      default="")
###################
## Choice 2      ##
###################
parser.add_argument("--executed",     help="if not use JSON file: simply generate the nb executed line in the lib/program",
                                      default=-1,
                                      type=int)
parser.add_argument("--executable",   help="if not use JSON file: simply generate the nb executable line in the lib/program",
                                      default=-1,
                                      type=int)
###################
## Choice 3      ##
###################
parser.add_argument("--test",         help="test value (local server ...)",
                                      action="store_true")
args = parser.parse_args()

if args.test == True:
	args.url = 'http://127.0.0.1/coverage/inject.php'
	args.repo = 'HeeroYui/test'
	args.sha1 = ''
	args.branch = 'master'
	json_data = '{"executed":16,"executable":512,"list":[{"file":"test/plop.cpp","executed":57,"executable":75}]}'
else:
	if args.json != "":
		if args.executed >= 0:
			print("[ERROR] (local) set 'executed' parameter with a json file")
			exit(-2)
		if args.executable >= 0:
			print("[ERROR] (local) set 'executable' parameter with a json file")
			exit(-2)
		if not os.path.isfile(args.json):
			print("[ERROR] (local) can not read json file" + args.json)
			exit(-2)
		file = open(args.json, "r")
		json_data = file.read()
		file.close()
		if len(json_data) <= 0:
			print("[ERROR] (local) json file is empty")
			exit(-2)
	else:
		if args.executed < 0:
			print("[ERROR] (local) missing 'executed' parameter with NO json file")
			exit(-2)
		if args.executable < 0:
			print("[ERROR] (local) missing 'executable' parameter with NO json file")
			exit(-2)
		# create the minimal json file:
		json_data = '{"executed":' + str(args.executed) + ',"executable":' + str(args.executable) + ',"list":[]}'

print("json data: " + str(json_data))

# todo : check if repo is contituated wit a "/" ...
# if repo, sha1 and branch is not set, we try to get it with travis global environement variable :
if args.repo == "":
	args.repo = os.environ.get('TRAVIS_REPO_SLUG')
	if args.repo == None:
		print("[ERROR] (local) missing 'repo' parameter can not get travis env variable")
		exit(-2)
if args.sha1 == "":
	args.sha1 = os.environ.get('TRAVIS_COMMIT')
	if args.sha1 == None:
		args.sha1 = ""

if args.branch == "":
	args.branch = os.environ.get('TRAVIS_BRANCH')
	if args.branch == None:
		args.branch = ""

print("    url = " + args.url)
print("    repo = " + args.repo)
print("    sha1 = " + args.sha1)
print("    branch = " + args.branch)
print("    json_data len = " + str(len(json_data)))

data = urllib.urlencode({'REPO':args.repo,
                         'SHA1':args.sha1,
                         'LIB_BRANCH':args.branch,
                         'JSON_FILE':json_data})
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
#print response.geturl()
#print response.info()
return_data = response.read()
print return_data
if return_data[:7] == "[ERROR]":
	exit(-1)

exit(0)

