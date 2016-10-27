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
import fnmatch
import time


parser = argparse.ArgumentParser()
parser.add_argument("-u", "--url",       help="server URL",
                                         default="http://atria-soft.com/ci/warning/inject")
parser.add_argument("-r", "--repo",      help="Curent repositoty (generic github name (userName/repoName)",
                                         default="")
parser.add_argument("-s", "--sha1",      help="Sha1 on the commit (git) (256 char limited)",
                                         default="")
parser.add_argument("-b", "--branch",    help="branch of the repository (default master)",
                                         default="")
###################
## Choice 1      ##
###################
parser.add_argument("-j", "--json",      help="all data to send ... (json file NOT json data)",
                                         default="")
###################
## Choice 2      ##
###################
parser.add_argument("--find-path",       help="recursive path finding all file with the regex value (--regex)",
                                         default="out/")
parser.add_argument("--extention",       help="file extention where is stored the data of warnings (default : 'warning')",
                                         default="warning")
parser.add_argument("--regex",           help="Filtering the path finding element (default '*')",
                                         default="*")
parser.add_argument("--rm-path",         help="remove path in the output file name",
                                         action="store_true")
###################
## Choice 3      ##
###################
parser.add_argument("--warning",         help="if not use JSON file: simply generate the nb warning lines in the lib/program",
                                         default=-1,
                                         type=int)
parser.add_argument("--error",           help="if not use JSON file: simply generate the nb error lines in the lib/program",
                                         default=-1,
                                         type=int)
###################
## Choice 4      ##
###################
parser.add_argument("--test",            help="test value (local server ...)",
                                         action="store_true")
args = parser.parse_args()

if args.test == True:
	args.url = 'http://127.0.0.1/ci/warning/inject.php'
	args.repo = 'HeeroYui/test'
	args.sha1 = ''
	args.branch = 'master'
	json_data = '{"count":5200,"list":[{"file":"test/plop.cpp","count-warning":57,"count-error":3}]}'
else:
	if args.json != "":
		if args.warning >= 0:
			print("[ERROR] (local) set 'warning' parameter with a json file")
			exit(-2)
		if args.error >= 0:
			print("[ERROR] (local) set 'error' parameter with a json file")
			exit(-2)
		if args.find_path >= 0:
			print("[ERROR] (local) set 'find-path' parameter with a json file")
			exit(-2)
		if args.regex >= 0:
			print("[ERROR] (local) set 'regex' parameter with a json file")
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
	elif args.warning >= 0:
		if args.find_path >= 0:
			print("[ERROR] (local) set 'find-path' parameter with 'count'")
			exit(-2)
		if args.regex >= 0:
			print("[ERROR] (local) set 'regex' parameter with 'count'")
			exit(-2)
		if args.error < 0:
			args.error = 0
		json_data = '{"warning":' + args.warning + ',"error":' + args.error + ',"list":[]}'
	else:
		if args.find_path == "":
			print("[ERROR] (local) set 'find-path' parameter empty")
			exit(-2)
		if args.regex == "":
			print("[ERROR] (local) set 'regex' parameter empty")
			exit(-2)
		# get all the requested files :
		all_files = []
		for root, dirnames, filenames in os.walk(args.find_path):
			# filter with extention ...
			filesss = fnmatch.filter(filenames, "*." + args.extention)
			for filename in filesss:
				file = os.path.join(root, filename)
				all_files.append(file)
		# filter with the regular expression
		all_files = fnmatch.filter(all_files, args.regex)
		all_files2 = []
		for elem in all_files:
			file = elem[len(args.find_path):-len(args.extention)-1]
			all_files2.append(file)
			print("file : " + file);
		all_files = all_files2
		result = [];
		total_warning_count = 0
		total_error_count = 0
		# parse all files needed:
		for elem in all_files:
			file = open(args.find_path + elem + "." + args.extention, "r")
			data = file.read()
			file.close()
			if len(data) == 0:
				# no warning and no error posible
				result.append([elem, 0, 0])
			else:
				lines = data.split("\n")
				error_count = 0
				warning_count = 0
				for line in lines:
					warning_count += line.count('warning:')
					error_count += line.count('error:')
				total_warning_count += warning_count
				total_error_count += error_count
				result.append([elem, warning_count, error_count])
		print("result : " + str(result))
		
		# create the minimal json file:
		json_data = '{\n\t"warning":' + str(total_warning_count) + ',\n\t"error":' + str(total_error_count) + ',\n\t"list":[\n'
		first = True
		for elem in result:
			if first == True:
				first = False
				json_data += '\t\t{\n'
			else:
				json_data += '\t\t}, {\n'
			json_data += '\t\t\t"file":"' + elem[0] + '",\n'
			json_data += '\t\t\t"warning":"' + str(elem[1]) + '",\n'
			json_data += '\t\t\t"error":"' + str(elem[2]) + '"\n'
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

