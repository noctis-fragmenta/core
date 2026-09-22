docker exec noctis-db mysqldump --no-tablespaces -u nf_user -p'nf_pass' chards > db-backups/chards_$(date +%Y%m%d_%H%M%S).sql
echo "Backup Saved"
