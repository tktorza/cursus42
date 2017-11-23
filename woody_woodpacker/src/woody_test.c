/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody_test.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/15 12:02:55 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/23 17:16:22 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/woody.h"
#include "../includes/elf.h"
#include "../includes/uint.h"
/*
char *ft_nimp(char *key, int nb)
{
	int size = ft_strlen(key);
	char *test = key;
	int c;
	char *str;
	
	if ((str = (char *)malloc(sizeof(char) * (size + 1))) == NULL)
		return (NULL);
	int i = 1;
	if (nb == 0)
	{
		str[0] = key[size / 3];
		while (i < size)
		{
			str[i] = key[i - 1];
			i++;
		}
	}
	else if (nb == 2)
	{
	i = 2;
		str[2] = key[size / 3];
		while (i < size)
		{
			c = key[i];
			str[i] = (char)(c - 15);
			i++;
		}
	}
	str[i] = '\0';
	return (str);
}

char	*create_key(Elf64_Ehdr *header, Elf64_Shdr *section, uint8_t *data, int *int_key)
{
	char *key;
	char *fake_start;
	int real_start;
	unsigned long long rand_start = &section[header->e_shnum % 3].sh_entsize;

	key =  ft_itoa_base(rand_start, 16);
	//taille de 9 à tj checker
	fake_start = ft_nimp(key, 0);
	real_start = ft_strlen(fake_start);
	//depart à strlen
	key = ft_strjoin(fake_start, key);
	
	// fprintf(stderr, " %llu === %s | %s -- > %s\n", rand_start, fake_start, &key[real_start], key);
	// fprintf(stderr, "key ? %s \n", key);
	for (int i =0;i < ft_strlen(key) + 1;i++)
	{
		printf("%c", key[i]);
	}
	printf("\n");
	fake_start = ft_nimp(fake_start, 2);
		
	for (int i =0;i < ft_strlen(key) + 1;i++)
	{
		printf("%c", key[i]);
	}
	printf("\n");
	
	// fprintf(stderr, "key=%s \n", key);
	
	ft_strjoin(key, fake_start);
	// fprintf(stderr, " key ?%s \n", key);
	// return (key);
	*int_key = 2;
	return ("2");
}
*/
void        print_error(char *file, char *str)
{
    printf("ft_nm: %s: %s.\n", file, str);
    exit (1);
}

void 	decypt()
{
	printf("...WOODY...\n");
}


void	open_woody(void *ptr, unsigned int size, int fd1, int fd2)
{
	int	fd;

	if ((fd = open("./woody", O_CREAT | O_RDWR | O_TRUNC, 0777)) <= 0)
		print_error("woody", "Error on open");
	write(fd, ptr, size);
	close(fd);
	close(fd1);
	close(fd2);
	printf("All is ok and files are close, test?\n");
}

void	encrypt_text(uint8_t *data, size_t k, int key)
{
	// printf("keyyyy ===== %d\n", key);
	data[k] = data[k] + key;
}

//segment
void	deplace_text_section(Elf64_Shdr *section, size_t i, struct stat buf, char *ptr, uint8_t *data)
{
	char str[section[i].sh_size + 1];
	int x = 0;
	for (size_t k = section[i].sh_offset; k < section[i].sh_offset + section[i].sh_size; ++k)
	{
		str[x] = data[k];
		x++;
	}
	size_t k = section[i].sh_offset;
	size_t size = section[i].sh_offset + ft_strlen(ptr);
	while (k <= size)
	{
		data[k] = *ptr;
		*ptr += 1;
		k++;
	}
	section[i].sh_size += size;
	while (k < section[i].sh_offset + section[i].sh_size)
	{
		data[k] = *str;
		*str += 1;
		k++;
	}
}

void    *open_decrypt(struct stat *buf, int *fd/*, int *gap*/)
{
    void 				*ptr;

    if ((*fd = open("./src/test", O_RDONLY)) < 0)
		print_error("./src/test", "No such file or directory");
	if (fstat(*fd, buf) < 0)
		print_error("./src/test", "Error with fstat");
	if ((ptr = mmap(0, buf->st_size, PROT_READ | PROT_WRITE, MAP_PRIVATE, *fd, 0))
	== MAP_FAILED)
        print_error("./src/test", "Is a directory");
    /**gap = buf->st_size;*/
	return (ptr);
}

Elf32_Addr	*loop_section_offset_free_for_decrypt(Elf64_Ehdr *header, Elf64_Shdr *section, char *sectname, uint8_t *data)
{
		struct stat			buf;
		int					fd;
		void 				*ptr;
	
		if ((fd = open("./src/test", O_RDONLY)) < 0)
			print_error("./src/test", "No such file or directory");
		if (fstat(fd, &buf) < 0)
			print_error("./src/test", "Error with fstat");
		if ((ptr = mmap(0, buf.st_size, PROT_READ | PROT_WRITE, MAP_PRIVATE, fd, 0))
		== MAP_FAILED)
			print_error("./src/test", "Is a directory");

	return (ptr);
}

Elf64_Phdr *elf_find_gap(void *ptr, int size, int *p, int *len)
{
    Elf64_Ehdr *elf_hdr = (void *)ptr;
    Elf64_Phdr *elf_seg, *text_seg;
    int         n_seg = elf_hdr->e_phnum;
    int text_end, gap=size;
    // struct stat buf;
    // char    *infect_addr;
    
    // infect_addr = (char *)open_decrypt(&buf, &gap);
    elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_hdr + (unsigned int) elf_hdr->e_phoff);

    for (size_t i = 0;i < n_seg;i++)
    {
        if (elf_seg->p_type == PT_LOAD && elf_seg->p_flags & 0x011)
        {
            printf("Segment .text found: #%lu\n", i);
			text_seg = elf_seg;
			//fin de seg text
            text_end = text_seg->p_offset + text_seg->p_filesz;
        }
        else
        {
			//si gap < size du file
          if (elf_seg->p_type == PT_LOAD && (elf_seg->p_offset - text_end) < gap) 
            {
				gap = elf_seg->p_offset - text_end;
              printf ("   * Found LOAD segment (#%d) close to .text (offset: 0x%x) --> gap(#%d)\n", i, (unsigned int)elf_seg->p_offset, gap);
            }
		}
		//on increment de elf_seg
          elf_seg = (Elf64_Phdr *) ((unsigned char*) elf_seg + (unsigned int) elf_hdr->e_phentsize);
	}
	
    *p = text_end;
    *len = gap;

    return (text_seg);
}

Elf64_Shdr *elf_find_section(void *ptr, char *name)
{
	Elf64_Ehdr *header;
	Elf64_Shdr *section;
	uint8_t *data;
	char *sectname;

	data = ptr;
    header = (void *)ptr;
    section = (void *)header + header->e_shoff;	
	sectname = (char*)(ptr + section[header->e_shstrndx].sh_offset);

	printf ("+ %d section in file. Looking for section '%s'\n", 
		header->e_shnum, name);
	
	for (size_t i = 0; i < header->e_shnum; i++)
	  {
		if (ft_strcmp(&sectname[section[i].sh_name], ".text") == 0 && section[i].sh_addr)
			return (&section[i]);
	  }
	return (NULL);
}

int		elf_mem_subst(void *m, int len, long pat, long val)
{
  unsigned char *p = (unsigned char*)m;
  long v;
  int i, r;

  for (i = 0; i < len; i++)
  {
	  v = *((long *)(p + i));
	  r = v ^ pat;

	  if (r == 0)
	  {
		  printf("+ Pattern %lx found at offset %d -> %lx\n", pat, i, val);
		  *((long *)(p + i)) = val;
		  return 0;
	  }
  }
  return -1;
}

void	woody_start(void *ptr, unsigned int size, int fd)
{
	int text_end = 0;
	int gap = 0;
	Elf64_Ehdr *header = (Elf64_Ehdr *)ptr;
	Elf64_Phdr	*t_text_seg = elf_find_gap(ptr, size, &text_end, &gap);
	Elf64_Addr	*base = t_text_seg->p_vaddr;
  
    printf ("+ .text segment gap at offset 0x%x(0x%x bytes available)\n", text_end, gap);
  
	struct stat buf;
	int		fd_infect;
	void		*inf_addr = open_decrypt(&buf, &fd_infect);
	Elf64_Shdr *p_text_sec = elf_find_section(inf_addr, ".text");
	printf ("+ Payload .text section found at %llx (%llx bytes)\n", 
	p_text_sec->sh_offset, p_text_sec->sh_size);

	if (p_text_sec->sh_size > gap)
	{
		fprintf (stderr, "- Payload to big, cannot infect file.\n");
		exit (1);
	}
	/* Copy payload in the segment padding area */
	ft_memmove (ptr + text_end, inf_addr + p_text_sec->sh_offset, p_text_sec->sh_size);
    // return text_seg;
    
	// key = create_key(header, section, data, &int_key);
	// loop_section_offset_free_for_decrypt(header, section, sectname, data);
	elf_mem_subst(ptr + text_end, p_text_sec->sh_size, 0x11111111, (long)header->e_entry);
	header->e_entry = (Elf64_Addr) (base + text_end);

	open_woody(ptr, size, fd, fd_infect);
}

static int			is_elf64(char *ptr)
{
	if (ptr[0] == ELFMAG0 && ptr[1] == ELFMAG1 && ptr[2] == ELFMAG2 &&
		ptr[3] == ELFMAG3 && ptr[4] == ELFCLASS64)
		return (1);
	return (0);
}

static void        *get_ptr(char *filename, unsigned int *size, int *fd)
{
	struct stat			buf;
	void 				*ptr;

	if ((*fd = open(filename, O_APPEND | O_RDWR, 0)) < 0)
		print_error(filename, "No such file or directory");
	if (fstat(*fd, &buf) < 0)
		print_error(filename, "Error with fstat");
	if ((ptr = mmap(0, buf.st_size, PROT_READ | PROT_WRITE | PROT_EXEC, MAP_SHARED, *fd, 0))
	== MAP_FAILED)
		print_error(filename, "Is a directory");
	*size = buf.st_size;
	return (ptr);
}

int         main(int ac, char **av)
{
	void 				*ptr;
	unsigned int		size;
	int					fd;
	
	if (ac == 2)
	{
		ptr = get_ptr(av[1], &size, &fd);
		if (!is_elf64((char *)ptr))
			print_error(av[1], "This is not a elf64");
		woody_start(ptr, size, fd);
	}
	else
		printf("Error: %s <filename>\n", av[0]);
	return (0);
}