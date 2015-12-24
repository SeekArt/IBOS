    FROM tutum/lamp:latest
    RUN rm -fr /app && git clone https://github.com/SeekArt/IBOS.git /app
    RUN chmod 755 /app
    
    ENV PHP_UPLOAD_MAX_FILESIZE 50M 
    ENV PHP_POST_MAX_SIZE 50M 

    EXPOSE 80 3306
    CMD ["/run.sh"]