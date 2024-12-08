terraform {
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0"
    }
  }
}

locals {
  docker_host = terraform.workspace == "prod" ? "ssh://${var.ssh_user}@${var.ssh_host}" : "npipe:////./pipe/docker_engine"
  provision   = terraform.workspace == "prod" ? 1 : 0
  command     = terraform.workspace == "prod" ? ["pnpm", "preview", "--host"] : ["pnpm", "dev", "--host"]
}

provider "docker" {
  host = local.docker_host
}

provider "null" {}

resource "null_resource" "build" {
  count = var.local_build ? 1 : 0

  provisioner "local-exec" {
    command = "docker build -t tsapi-v2:${var.v} ."
  }
}

resource "null_resource" "copy" {
  count = local.provision

  provisioner "local-exec" {
    command = file("pack.bat")
  }

  provisioner "file" {
    source      = "portal.tar.gz"
    destination = "/tmp/portal.tar.gz"
    connection {
      agent = true
      host  = var.ssh_host
      user  = var.ssh_user
    }
  }

  # provisioner "remote-exec" {
  #   inline = [
  #     "rm -rf /tmp/tsapi-v2-portal-build",
  #     "echo 2",
  #     "mkdir -p /tmp/tsapi-v2-portal-build",
  #     "echo 3",
  #     "cd /tmp/tsapi-v2-portal-build",
  #     "echo 4",
  #     "tar -xf /tmp/portal.tar.gz .",
  #     "echo 5",
  #     "rm /tmp/portal.tar.gz",
  #     "echo \"1\""
  #   ]
  #   connection {
  #     agent = true
  #     host  = var.ssh_host
  #     user  = var.ssh_user
  #   }
  # }
}

resource "docker_image" "image" {
  count      = local.provision
  depends_on = [null_resource.copy]

  name = "tsapi-v2-portal:${var.v}"
  build {
    context = "/tmp/tsapi-v2-portal-build"
    # context = "."
  }
}

resource "docker_container" "portal" {
#   count = local.provision

  name  = "tsapi-v2-portal"
  image = "tsapi-v2-portal:${var.v}"
  ports {
    internal = terraform.workspace == "prod" ? 4173 : 5173
    external = var.portal_port
  }
  depends_on = [docker_image.image]
  env = [
    "TSP_API_URL=${var.api_url}"
  ]
  volumes {
    host_path      = "${path.cwd}/src"
    container_path = "/app/src"
  }
  command = local.command
}

output "provision_value" {
  value = local.provision
}

