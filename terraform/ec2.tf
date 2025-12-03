
resource "aws_instance" "web" {
  depends_on = [
    aws_db_instance.rds
  ]

  ami                    = "ami-0c2b8ca1dad447f8a" # Amazon Linux 2
  instance_type          = var.instance_type
  vpc_security_group_ids = [aws_security_group.web_sg.id]

  user_data = templatefile("user_data.sh" , {
    db_endpoint = aws_db_instance.rds.endpoint
    db_username = var.db_username
    db_password = var.db_password
  })

  tags = {
    Name = "systechpump"
  }
}