printf APPUID=$(id -u)  > .env 
echo "" >> .env
printf APPUGID=$(id -g)>> .env
