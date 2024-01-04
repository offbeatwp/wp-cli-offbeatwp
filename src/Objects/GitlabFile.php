<?php

namespace OffbeatCLI\Objects;

final class GitlabFile
{
    public string $id;
    public string $name;
    public string $path;
    public string $type;
    public string $mode;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->path = $data['path'];
        $this->mode = $data['mode'];
    }
}
