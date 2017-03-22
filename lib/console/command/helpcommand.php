<?php

namespace WS\ReduceMigrations\Console\Command;

class HelpCommand extends BaseCommand {

    public function execute($callback = false) {
        $this->console
            ->printLine("Action list:")
            ->printLine("* list - List of new migrations")
            ->printLine("* apply - Apply new migrations, \n   -f Without approve")
            ->printLine("* rollback - Rollback last applied migrations")
            ->printLine("* createScenario - Create new migration scenario, \n  -n 'Name' - scenario name \n  -d 'Description' - scenario description")
            ->printLine("* last - Show last applied migrations list")
        ;
    }
}
