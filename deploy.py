import sys
import os
import os.path
import shutil
import time

def destination():
   return "/Library/WebServer/Documents/mud/"

def full_list():
   all_files = os.listdir(".")
   accepted_files = []
   for f in all_files:
      if f.endswith('.php'):
         accepted_files.append(f)
      if f.endswith('.js'):
         accepted_files.append(f)
      if f.endswith('.css'):
         accepted_files.append(f)
      if f.endswith('.html'):
         accepted_files.append(f)
   return accepted_files

def needs_to_be_deployed(f):
   if not os.path.exists(os.path.join(destination(), f)):
      return True
   local_time = os.path.getmtime(f)
   remote_time = os.path.getmtime(os.path.join(destination(), f))
   return local_time>remote_time

def transfer_list_filter(full_list):
   answer = []
   for f in full_list:
      if needs_to_be_deployed(f):
         answer.append(f)
   return answer

# shutil.copy(full_file_name, dest)
def deploy_to_website(source):
   dest = os.path.join(destination(), source)
   print("%s Deploying %s" % (time.time(), source))
   shutil.copy(source, dest)

def main():
   while (True):
      time.sleep(0.5)
      for source in transfer_list_filter(full_list()):
         deploy_to_website(source)
      
	
if __name__ == '__main__':
   main()
