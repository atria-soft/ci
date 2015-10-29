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


parser = argparse.ArgumentParser()
parser.add_argument("-u", "--url",    help="server URL",
                                      default="http://atria-soft.com/ci/test/inject")
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
parser.add_argument("--passed",       help="if not use JSON file: nb Test passsed in the lib/program",
                                      default=-1,
                                      type=int)
parser.add_argument("--total",        help="if not use JSON file: number total of test in the lib/program",
                                      default=-1,
                                      type=int)
###################
## Choice 3      ##
###################
parser.add_argument("--file",         help="if not use JSON file: file with the 'gtest' log",
                                      default="")
###################
## Choice 4      ##
###################
parser.add_argument("--test",         help="test value (local server ...)",
                                      action="store_true")
args = parser.parse_args()

if args.test == True:
	args.url = 'http://127.0.0.1/ci/test_inject.php'
	args.repo = 'HeeroYui/test'
	args.sha1 = ''
	args.branch = 'master'
	json_data = '{"executed":16,"executable":512,"list":[{"file":"test/plop.cpp","executed":57,"executable":75}]}'
else:
	if args.json != "":
		if args.passed >= 0:
			print("[ERROR] (local) set 'passed' parameter with a json file")
			exit(-2)
		if args.total >= 0:
			print("[ERROR] (local) set 'total' parameter with a json file")
			exit(-2)
		if args.file != "":
			print("[ERROR] (local) set 'file' parameter with a json file")
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
	elif args.passed >= 0:
		if args.file != "":
			print("[ERROR] (local) set 'file' parameter with 'passed'")
			exit(-2)
		if args.total < 0:
			args.total = 0
		json_data = '{"passed":' + args.passed + ',"total":' + args.total + ',"list":[]}'
	else:
		if args.file == "":
			print("[ERROR] (local) set 'file' parameter empty")
			exit(-2)
		if not os.path.isfile(args.file):
			print("[ERROR] (local) can not read test file" + args.file)
			exit(-2)
		file = open(args.file, "r")
		data = file.read()
		file.close()
		lines = data.split("\n")
		result = []
		total_test = 0
		total_passed = 0
		# parse all lines
		for line in lines:
			if len(line) <= 0:
				continue
			if line[0] != '[':
				continue
			if line[:12] == "[ RUN      ]":
				#next line is usefull ...
				continue
			if line[:12] == "[  PASSED  ]":
				#End of test result
				break
			if line[:12] == "[       OK ]":
				# this test is OK ...
				# TestDeclaration.testBase (0 ms)
				test_name = line[13:].split(" ")[0]
				result.append([test_name, True])
				total_test += 1
				total_passed += 1
				continue
			if line[:12] == "[  FAILED  ]":
				# this test failled ...
				test_name = line[13:].split(" ")[0]
				result.append([test_name, False])
				total_test += 1
				continue
			# nothing to do ...
		#print("result : " + str(result))
		
		# create the minimal json file:
		json_data = '{\n\t"passed":' + str(total_passed) + ',\n\t"total":' + str(total_test) + ',\n\t"list":[\n'
		first = True
		for elem in result:
			if first == True:
				first = False
				json_data += '\t\t{\n'
			else:
				json_data += '\t\t}, {\n'
			json_data += '\t\t\t"test-name":"' + elem[0] + '",\n'
			if elem[1] == True:
				json_data += '\t\t\t"fail":false\n'
			else:
				json_data += '\t\t\t"fail":true\n'
		if first == False:
			json_data += '\t\t}\n'
		json_data += '\t]\n}'

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
                         'JSON_FILE':json_data,
                         'LIB_BRANCH':args.branch})

req = urllib2.Request(args.url, data)
response = urllib2.urlopen(req)
#print response.geturl()
#print response.info()
return_data = response.read()
print return_data
if return_data[:7] == "[ERROR]":
	exit(-1)

exit(0)

