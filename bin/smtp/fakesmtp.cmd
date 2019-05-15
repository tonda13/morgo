@echo off
start java -jar C:\sw\faceSMTP\fakeSMTP-2.0.jar -s -b -p 25 -a 127.0.0.1 -o L:\_emails
exit