#!/bin/bash

ssh -R 80:localhost:5147 serveo.net -o ConnectTimeout=1200
