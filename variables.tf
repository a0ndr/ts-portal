variable "ssh_host" {
  description = "The IP/Hostname of the remote machine"
  type        = string
  default     = ""
  sensitive   = true
}

variable "ssh_user" {
  description = "The username of the user that can use docker"
  type        = string
  default     = ""
  sensitive   = true
}

variable "v" {
  description = "Docker image version"
  type        = string
  default     = "latest"
}

variable "portal_port" {
  description = "The port number"
  type        = number
  default     = 4173
}

variable "api_url" {
  description = "The URL of tsapi-v2"
  type        = string
  default     = "http://localhost:3000"
}

variable "local_build" {
  description = "Enable building the docker image on startup in Local mode"
  type        = bool
  default     = false
}
