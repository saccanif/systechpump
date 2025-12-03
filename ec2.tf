
resource "aws_instance" "web" {
  ami                    = "ami-0c2b8ca1dad447f8a" # Amazon Linux 2
  instance_type          = var.instance_type
  vpc_security_group_ids = [aws_security_group.web_sg.id]

  user_data = templatefile("user_data.sh" , {
    db_endpoint = "mydb.xxxxx.us-east-1.rds.amazonaws.com"
  })

  tags = {
    Name = "systechpump"
  }
}
