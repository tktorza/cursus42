/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   woody.h                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/11/14 16:37:57 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/23 18:05:49 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef WOODY_H
# define WOODY_H

#include <sys/types.h>
#include <sys/stat.h>
#include <sys/mman.h>
#include <unistd.h>
#include <fcntl.h>
#include <stdio.h>
# include "../libft/inc/libft.h"
# include "../libft/inc/ft_printf.h"
# include "./elf.h"

void    *open_decrypt(struct stat *buf, int *fd/*, int *gap*/);
void    woody_start(void *ptr, unsigned int size, int fd);
Elf64_Phdr *elf_find_gap(void *ptr, int size, int *p, int *len);
Elf64_Shdr *elf_find_section(void *ptr, char *name);
int		elf_mem_subst(void *m, int len, long pat, long val);
void	open_woody(void *ptr, unsigned int size, int fd1, int fd2);


#endif