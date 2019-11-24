Docker Containers
=================

You need to install and configure docker beforehand.

MacOS + VirtualBox
------------------ 
```bash
eval $(docker-machine env dev) # dev = name of your virtual machine.
```

---------

# Backend

This container is for Symfony/VueJS deployments. 
It does not contain any node modules or node binaries.

## Building

```bash
docker build -t registry.gitlab.com/jkwebgmbh/symfony4-skeleton/backend deploy/docker/backend
```

## Deploying your build

```bash
docker push registry.gitlab.com/jkwebgmbh/symfony4-skeleton/backend
```


# Fullstack

`ATTENTION:` This container depends on the backend-container. 
Make sure the backend-container is already updated, built and pushed.

This container is for a simple Symfony/Twig/JavaScript deployment.
Where everything is in one simple container.

## Building

```bash
docker build -t registry.gitlab.com/jkwebgmbh/symfony4-skeleton/fullstack deploy/docker/fullstack
```

## Deploying your build

```bash
docker push registry.gitlab.com/jkwebgmbh/symfony4-skeleton/fullstack
```
