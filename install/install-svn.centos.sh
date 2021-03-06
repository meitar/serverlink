#!/bin/sh -e
error()
{
  echo ERROR${1+: }"$@" >&2
  trap - EXIT
  exit 1
}
trap 'error' EXIT
cat >files/manage-svn-0.0.1-grg/etc/authorized_keys <<EOF
command="exec /usr/local/sbin/manage-svn",no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty `cat /opt/webenabled/config/ssh/manage-svn.pub`
command="exec /usr/local/sbin/manage-repo-system",no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty `cat /opt/webenabled/config/ssh/manage-repo-system.pub`
EOF
cd files/manage-svn-0.0.1-grg
./install-ce-svn.sh svn.`hostname`
apachectl configtest
apachectl graceful

trap - EXIT
echo 'ALL DONE (svn)'
