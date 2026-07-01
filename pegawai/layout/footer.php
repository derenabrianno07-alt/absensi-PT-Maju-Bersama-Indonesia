    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateClock(){
    const el=document.getElementById('jamDigital');
    if(!el)return;
    const n=new Date();
    el.textContent=String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0')+':'+String(n.getSeconds()).padStart(2,'0');
}
setInterval(updateClock,1000);
updateClock();
</script>
<?= get_alert() ?>
</body>
</html>
