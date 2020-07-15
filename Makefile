init-dev:
	# add custom precommit hook:
	chmod +x .githooks/pre-commit
	grep -vwE ".githooks/pre-commit" .git/hooks/pre-commit > .git/hooks/pre-commit.tmp; mv .git/hooks/pre-commit.tmp .git/hooks/pre-commit
	tail -c1 .githooks/pre-commit | read -r _ || echo >> .githooks/pre-commit
	echo 'unset GIT_DIR; cd $$(git rev-parse --show-toplevel); .githooks/pre-commit;' >> .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit
