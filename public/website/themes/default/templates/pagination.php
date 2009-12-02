<div class="pagination" style="width:100%">
    <div style="float:left;width:28%">
    </div>
    <div style="float:right;width:70%;">
        <!-- First page link -->
        <?php if (isset($this->previous)): ?>
              <a href="<?= $this->url(array('page' => $this->first)); ?>">Start</a> |
        <?php else: ?>
                <span class="disabled">Start</span> |
        <?php endif; ?>
    
        <!-- Previous page link -->
    
        <?php if (isset($this->previous)): ?>
              <a href="<?= $this->url(array('page' => $this->previous)); ?>">&lt; Previous</a> |
        <?php else: ?>
            <span class="disabled">&lt; Previous</span> |
        <?php endif; ?>
        <!-- Numbered page links -->
        <?php foreach ($this->pagesInRange as $page): ?>
            <?php if ($page != $this->current): ?>
                <a href="<?= $this->url(array('page' => $page)); ?>"><?= $page; ?></a>
            <?php else: ?>
                <?= $page; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <!-- Next page link -->
        <?php if (isset($this->next)): ?>
              | <a href="<?= $this->url(array('page' => $this->next)); ?>">Next &gt;</a> |
        <?php else: ?>
            | <span class="disabled">Next &gt;</span> |
        <?php endif; ?>
        <!-- Last page link -->
        <?php if (isset($this->next)): ?>
              <a href="<?= $this->url(array('page' => $this->last)); ?>">End</a>
        <?php else: ?>
            <span class="disabled">End</span>
        <?php endif; ?>
        &nbsp; Page <?= $this->current; ?> of <?= $this->last; ?>
    </div>
 </div>