# .bashrc

# Source global definitions
if [ -f /etc/bashrc ]; then
        . /etc/bashrc
fi

# User specific aliases and functions

#cd configs
alias cdbin='cd /home/tchristian/u/bin/;pwd'
alias cdlib='cd /home/tchristian/u/lib/;pwd'
alias cdprd='cd /home/tchristian/u/prod/html;pwd'
alias cdops='cd /home/tchristian/u/alpha/html;pwd'
alias cdilib='cd /home/tchristian/u/lib/5010/e837i/;pwd'
alias cdplib='cd /home/tchristian/u/lib/5010/e837p/;pwd'
alias cdlog='cd /u/spool/logs/;pwd'

#vi configs
alias vi='vim'
alias viewm='vim -R $1'

#Java Editing
alias cdjsrc='cd /home/tchristian/u/bin/jsrc/;pwd'
#alias cdbcp='cd /home/tchristian/u/bin/jsrc/$1;gradle b u;cd -'
#alias cdrcp='cd /home/tchristian/u/bin/jsrc/$1/repos/;java -jar *.jar'
alias cdecp='cd /home/tchristian/u/bin/jsrc/elig/src/main/java/com/claimlogic/elig/'

#GoLang Editing
alias cdGo='cd $HOME/GoLang/'

#git
alias gitdiff='git diff $1'
alias gitlog='git log $1'

#grep
alias fgrep='fgrep -s
