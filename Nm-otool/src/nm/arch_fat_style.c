#include "../../includes/nm_tool.h"

uint32_t     swap_uint32(struct fat_header *fheader, uint32_t val)
{
    if (fheader->magic == FAT_MAGIC)
        return (val);
    val = ((val << 8) & 0xFF00FF00) | ((val >> 8) & 0xFF00FF);
    return (val << 16) | (val >> 16);
}

void handle_fat(char *ptr, char *file, t_symtab *symt)
{
    struct fat_header    *fheader;
    struct fat_arch      *arch;
    uint32_t i;
    uint32_t offset;

    fheader = (struct fat_header *) (void *)ptr;
    i = swap_uint32(fheader, fheader->nfat_arch);
    arch = (struct fat_arch *) ((void *)ptr + sizeof(struct fat_header));
    while (i)
    {
        if (swap_uint32(fheader, arch->cputype) == CPU_TYPE_X86_64)
            offset = arch->offset;
        arch += sizeof(arch) / sizeof(void*);
        // arch += arch->size  + arch->offset;
        i--;
    }
    type_bin(ptr + swap_uint32(fheader, offset), file, symt);
}